<?php

namespace App\Services;

use App\Models\ApprenticeProfile;
use App\Models\EvidenceSupport;
use App\Models\Observation;
use App\Models\User;
use App\Models\WelfareCase;
use App\Repositories\Contracts\ApprenticeRepositoryInterface;
use App\Repositories\Contracts\ObservationRepositoryInterface;
use DomainException;

class InstructorService
{
    public function __construct(
        private readonly ApprenticeRepositoryInterface $apprenticeRepository,
        private readonly ObservationRepositoryInterface $observationRepository,
        private readonly RiskEngineService $riskEngineService,
        private readonly BienestarService $bienestarService,
        private readonly NotificationService $notificationService,
        private readonly AuditService $auditService
    ) {}

    public function getInstructorWorkspaceData(User $instructor, array $filters = []): array
    {
        $profile = $instructor->instructorProfile()->with([
            'fichas.trainingProgram',
            'fichas.sede',
            'fichas.academicPeriod',
            'fichas.apprentices.user',
        ])->first();

        $assignedFichas = $profile ? $profile->fichas : collect();
        $fichaIds = $assignedFichas->pluck('id')->all();

        $apprentices = !empty($fichaIds)
            ? $this->apprenticeRepository->getByFichaIds($fichaIds, $filters)
            : $this->apprenticeRepository->getAllWithRelations($filters);

        $observations = !empty($fichaIds)
            ? $this->observationRepository->getForFichaIds($fichaIds, 80)
            : collect();

        return [
            'instructorProfile' => $profile,
            'assignedFichas' => $assignedFichas,
            'apprentices' => $apprentices,
            'recentObservations' => $observations,
            'totalAssignedHours' => $assignedFichas->sum(fn ($f) => $f->pivot->hours_assigned ?? 0),
        ];
    }

    /**
     * Carga ágil individual o masiva de observaciones, inasistencias o llamados de atención
     * para una ficha asignada, alimentando en tiempo real el cálculo de riesgo del aprendiz.
     */
    public function recordObservationsBulkOrSingle(array $data, User $author): int
    {
        $targetApprenticeIds = [];

        if (($data['target_mode'] ?? 'single') === 'ficha_massive' && !empty($data['ficha_id'])) {
            $fichaApprentices = $this->apprenticeRepository->getByFichaIds([(int) $data['ficha_id']]);
            $targetApprenticeIds = $fichaApprentices->pluck('id')->all();
        } elseif (!empty($data['apprentice_ids']) && is_array($data['apprentice_ids'])) {
            $targetApprenticeIds = array_map('intval', $data['apprentice_ids']);
        } elseif (!empty($data['apprentice_profile_id'])) {
            $targetApprenticeIds = [(int) $data['apprentice_profile_id']];
        }

        if (empty($targetApprenticeIds)) {
            throw new DomainException('Debes seleccionar al menos un aprendiz o una ficha con aprendices para registrar la novedad.');
        }

        $isMassive = count($targetApprenticeIds) > 1 || ($data['target_mode'] ?? '') === 'ficha_massive';
        $createdCount = 0;

        foreach ($targetApprenticeIds as $apprenticeId) {
            $apprentice = $this->apprenticeRepository->findByIdWithContext($apprenticeId);
            if (!$apprentice) {
                continue;
            }

            // Si es inasistencia, actualizar métrica de inasistencias y tasa de asistencia del aprendiz
            if ($data['type'] === 'inasistencia') {
                $newAbsences = $apprentice->absences_count + 1;
                $newAttendance = max(40.0, round($apprentice->attendance_rate - 3.5, 2));
                $this->apprenticeRepository->updateAcademicMetrics($apprentice->id, [
                    'absences_count' => $newAbsences,
                    'attendance_rate' => $newAttendance,
                ]);
            }

            // Si viene con ajuste de nota / promedio académico opcional
            if (!empty($data['academic_average'])) {
                $this->apprenticeRepository->updateAcademicMetrics($apprentice->id, [
                    'academic_average' => (float) $data['academic_average'],
                ]);
            }

            $obs = $this->observationRepository->create([
                'apprentice_profile_id' => $apprentice->id,
                'ficha_id' => $apprentice->ficha_id,
                'author_id' => $author->id,
                'type' => $data['type'],
                'category' => $data['category'],
                'severity' => (int) ($data['severity'] ?? 2),
                'title' => $data['title'],
                'description' => $data['description'],
                'is_massive' => $isMassive,
                'escalated_to_welfare' => !empty($data['escalate_immediately']),
                'escalated_at' => !empty($data['escalate_immediately']) ? now() : null,
            ]);

            // Recalcular en tiempo real el semáforo de riesgo del aprendiz
            $updatedProfile = $this->riskEngineService->recalculateForApprentice($apprentice->id, true);

            // Notificar al aprendiz de la nueva novedad en su perfil
            if ($apprentice->user_id) {
                $severityBadge = $data['type'] === 'positiva' ? 'success' : ($obs->severity >= 4 ? 'danger' : 'warning');
                $this->notificationService->notifyUser(
                    $apprentice->user_id,
                    "Nueva {$obs->typeLabel()}: {$obs->title}",
                    "El instructor {$author->name} registró una novedad en tu expediente. Tu puntaje global de riesgo actual es {$updatedProfile->global_risk_score}%. Puedes adjuntar soportes o excusas si aplica.",
                    'observacion',
                    $severityBadge,
                    route('aprendiz.dashboard')
                );
            }

            // Si el instructor activó escalamiento directo al registrar la novedad
            if (!empty($data['escalate_immediately'])) {
                $this->escalateObservationToWelfare($obs->id, $author, $data['escalation_notes'] ?? null);
            }

            $createdCount++;
        }

        $this->auditService->log(
            'Instructor · Carga de Novedades',
            ($isMassive ? "Carga masiva ({$createdCount} aprendices)" : "Carga individual") . " de novedad tipo '{$data['type']}': {$data['title']}",
            $data['severity'] >= 4 ? 'warning' : 'info',
            'Observation',
            null,
            null,
            [
                'type' => $data['type'],
                'category' => $data['category'],
                'severity' => $data['severity'],
                'affected_apprentices' => $createdCount,
            ]
        );

        return $createdCount;
    }

    /**
     * Escalamiento directo en 1 clic: deriva casos críticos directamente al equipo de Bienestar.
     */
    public function escalateObservationToWelfare(int $observationId, User $instructor, ?string $customNotes = null): WelfareCase
    {
        $obs = $this->observationRepository->findById($observationId);
        if (!$obs) {
            throw new DomainException('La observación que intentas escalar no existe.');
        }

        if ($obs->welfareCase) {
            return $obs->welfareCase;
        }

        $this->observationRepository->markEscalated($obs->id);

        $diagnosis = "Caso derivado directamente por el instructor {$instructor->name} desde observación [{$obs->typeLabel()} - Severidad {$obs->severity}/5]: {$obs->description}";
        if ($customNotes) {
            $diagnosis .= "\n\nNota de Escalamiento del Instructor: {$customNotes}";
        }

        $case = $this->bienestarService->createInterventionCase([
            'apprentice_profile_id' => $obs->apprentice_profile_id,
            'origin_observation_id' => $obs->id,
            'risk_type' => $obs->category,
            'priority' => $obs->severity >= 4 ? 'critica' : 'alta',
            'status' => 'abierto',
            'title' => "Escalamiento Directo: {$obs->title}",
            'initial_diagnosis' => $diagnosis,
            'intervention_route' => 'Valoración prioritaria por equipo interdisciplinario de Bienestar al Aprendiz.',
            'commitments' => 'Asistir a citación prioritaria con Psicología / Trabajo Social.',
        ], $instructor->id);

        // Recalcular riesgo tras escalamiento
        $this->riskEngineService->recalculateForApprentice($obs->apprentice_profile_id, true);

        // Notificar a todos los Gestores de Bienestar
        $apprenticeName = $obs->apprenticeProfile?->user?->name ?? 'Aprendiz';
        $this->notificationService->notifyRole(
            User::ROLE_BIENESTAR,
            "Alerta de Escalamiento Directo [{$case->case_code}]",
            "El instructor {$instructor->name} escaló un caso crítico del aprendiz {$apprenticeName}: {$obs->title}.",
            'escalamiento',
            'danger',
            route('bienestar.dashboard')
        );

        $this->auditService->log(
            'Instructor · Escalamiento Directo',
            "Derivó observación ID {$obs->id} del aprendiz '{$apprenticeName}' a Bienestar -> Caso [{$case->case_code}]",
            'critical',
            'WelfareCase',
            $case->id,
            ['escalated_to_welfare' => false],
            ['escalated_to_welfare' => true, 'case_code' => $case->case_code]
        );

        return $case;
    }

    /**
     * Historial contextual: expediente académico y disciplinario del aprendiz limitado a su competencia.
     */
    public function getApprenticeContextualDossier(int $apprenticeProfileId, User $viewer): ApprenticeProfile
    {
        $apprentice = $this->apprenticeRepository->findByIdWithContext($apprenticeProfileId);
        if (!$apprentice) {
            throw new DomainException('Expediente de aprendiz no encontrado.');
        }

        if ($viewer->role === User::ROLE_INSTRUCTOR) {
            $instructorFichaIds = $viewer->instructorProfile?->fichas()->pluck('fichas.id')->all() ?? [];
            if (!empty($instructorFichaIds) && !in_array($apprentice->ficha_id, $instructorFichaIds, true)) {
                throw new DomainException('Acceso restringido: Solo puedes consultar el expediente contextual de aprendices pertenecientes a tus fichas asignadas.');
            }
        }

        return $apprentice;
    }

    /**
     * Permite al Instructor o Gestor de Bienestar validar un soporte/excusa médica cargada por el aprendiz,
     * mitigando automáticamente la inasistencia o novedad y recalculando su nivel de riesgo.
     */
    public function reviewEvidenceSupport(int $supportId, string $status, ?string $reviewNotes, User $reviewer): EvidenceSupport
    {
        $support = $this->observationRepository->updateSupportStatus($supportId, $status, $reviewer->id, $reviewNotes);
        $this->riskEngineService->recalculateForApprentice($support->apprentice_profile_id, true);

        if ($support->apprenticeProfile?->user_id) {
            $this->notificationService->notifyUser(
                $support->apprenticeProfile->user_id,
                "Soporte '{$support->title}' " . strtoupper($status),
                "Tu evidencia fue revisada por {$reviewer->name} con estado: " . strtoupper($status) . ($reviewNotes ? ". Observación: {$reviewNotes}" : ''),
                'soporte',
                $status === 'aprobado' ? 'success' : 'warning',
                route('aprendiz.dashboard')
            );
        }

        $this->auditService->log(
            'Expediente · Revisión de Soporte',
            "Marcó soporte ID {$support->id} como '{$status}' (Recálculo automático de riesgo ejecutado)",
            'info',
            'EvidenceSupport',
            $support->id,
            ['status' => 'pendiente'],
            ['status' => $status, 'review_notes' => $reviewNotes]
        );

        return $support;
    }
}
