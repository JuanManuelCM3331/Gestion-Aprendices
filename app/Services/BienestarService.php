<?php

namespace App\Services;

use App\Models\WelfareCase;
use App\Models\WelfareCaseFollowup;
use App\Repositories\Contracts\ApprenticeRepositoryInterface;
use App\Repositories\Contracts\MasterDataRepositoryInterface;
use App\Repositories\Contracts\RiskParameterRepositoryInterface;
use App\Repositories\Contracts\WelfareCaseRepositoryInterface;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BienestarService
{
    public function __construct(
        private readonly ApprenticeRepositoryInterface $apprenticeRepository,
        private readonly RiskParameterRepositoryInterface $parameterRepository,
        private readonly WelfareCaseRepositoryInterface $welfareCaseRepository,
        private readonly MasterDataRepositoryInterface $masterDataRepository,
        private readonly RiskEngineService $riskEngineService,
        private readonly NotificationService $notificationService,
        private readonly AuditService $auditService
    ) {}

    public function getMonitoringDashboardData(array $filters = []): array
    {
        return [
            'apprentices' => $this->apprenticeRepository->getAllWithRelations($filters),
            'stats' => $this->apprenticeRepository->getSemaphoreStats($filters),
            'parameters' => $this->parameterRepository->getAllOrdered(),
            'cases' => $this->welfareCaseRepository->getAllWithRelations($filters),
            'fichas' => $this->masterDataRepository->getFichasWithRelations(),
            'programs' => $this->masterDataRepository->getTrainingPrograms(),
            'sedes' => $this->masterDataRepository->getSedes(),
        ];
    }

    /**
     * Ajusta los pesos y parámetros del algoritmo sin tocar código fuente y recalcula el semáforo en vivo.
     */
    public function updateRiskModelParameters(array $weightsById, int $updatedBy): int
    {
        $oldWeights = $this->parameterRepository->getKeyedWeights();

        foreach ($weightsById as $parameterId => $weightValue) {
            $this->parameterRepository->updateWeight((int) $parameterId, (float) $weightValue, $updatedBy);
        }

        $newWeights = $this->parameterRepository->getKeyedWeights();
        $recalculatedCount = $this->riskEngineService->recalculateAllApprentices(true);

        $this->auditService->log(
            'Bienestar · Motor Predictivo',
            "Ajustó pesos y umbrales del algoritmo predictivo de riesgo ({$recalculatedCount} aprendices recalculados en tiempo real)",
            'critical',
            'RiskParameter',
            null,
            $oldWeights,
            $newWeights
        );

        return $recalculatedCount;
    }

    public function createInterventionCase(array $data, int $openedByUserId): WelfareCase
    {
        $code = 'BIE-' . date('Y') . '-' . strtoupper(Str::random(5));

        $case = $this->welfareCaseRepository->createCase([
            'case_code' => $code,
            'apprentice_profile_id' => $data['apprentice_profile_id'],
            'origin_observation_id' => $data['origin_observation_id'] ?? null,
            'opened_by' => $openedByUserId,
            'assigned_to' => $data['assigned_to'] ?? $openedByUserId,
            'risk_type' => $data['risk_type'],
            'priority' => $data['priority'],
            'status' => $data['status'] ?? 'en_ruta',
            'title' => $data['title'],
            'initial_diagnosis' => $data['initial_diagnosis'],
            'intervention_route' => $data['intervention_route'] ?? null,
            'commitments' => $data['commitments'] ?? null,
            'improvement_plan' => $data['improvement_plan'] ?? null,
            'due_date' => $data['due_date'] ?? now()->addDays(15)->toDateString(),
        ]);

        $apprentice = $this->apprenticeRepository->findByIdWithContext((int) $data['apprentice_profile_id']);
        if ($apprentice && $apprentice->user_id) {
            $this->notificationService->notifyUser(
                $apprentice->user_id,
                "Citación / Ruta de Bienestar Activa [{$case->case_code}]",
                "Se ha abierto la ruta de atención '{$case->title}' (Prioridad: " . strtoupper($case->priority) . "). Revisa tus compromisos y plan de mejora en tu portal.",
                'citacion_bienestar',
                $case->priority === 'critica' ? 'danger' : 'warning',
                route('aprendiz.dashboard')
            );
        }

        $this->auditService->log(
            'Bienestar · Trazabilidad de Casos',
            "Abrió caso de intervención [{$case->case_code}] para aprendiz ID {$case->apprentice_profile_id}",
            'warning',
            'WelfareCase',
            $case->id,
            null,
            $case->only(['case_code', 'risk_type', 'priority', 'status', 'title'])
        );

        return $case;
    }

    public function updateCaseAndFollowup(int $caseId, array $data, int $userId): WelfareCase
    {
        $case = $this->welfareCaseRepository->findById($caseId);
        if (!$case) {
            throw new DomainException('El caso de bienestar solicitado no existe.');
        }

        $oldValues = $case->only(['status', 'priority', 'intervention_route', 'commitments', 'improvement_plan', 'resolution_notes']);

        $updatePayload = [
            'status' => $data['status'],
            'priority' => $data['priority'] ?? $case->priority,
            'intervention_route' => $data['intervention_route'] ?? $case->intervention_route,
            'commitments' => $data['commitments'] ?? $case->commitments,
            'improvement_plan' => $data['improvement_plan'] ?? $case->improvement_plan,
            'resolution_notes' => $data['resolution_notes'] ?? $case->resolution_notes,
            'resolved_at' => $data['status'] === 'resuelto' ? ($case->resolved_at ?? now()) : null,
        ];

        $updatedCase = $this->welfareCaseRepository->updateCase($caseId, $updatePayload);

        if (!empty($data['followup_notes'])) {
            $this->welfareCaseRepository->addFollowup($caseId, [
                'user_id' => $userId,
                'stage' => $data['followup_stage'] ?? 'Seguimiento de Ruta y Compromisos',
                'notes' => $data['followup_notes'],
                'next_followup_date' => $data['next_followup_date'] ?? null,
            ]);
        }

        if ($updatedCase->apprenticeProfile?->user_id) {
            $this->notificationService->notifyUser(
                $updatedCase->apprenticeProfile->user_id,
                "Actualización en Caso de Bienestar [{$updatedCase->case_code}]",
                "El estado de tu ruta de atención ahora es: {$updatedCase->statusLabel()}.",
                'citacion_bienestar',
                $updatedCase->status === 'resuelto' ? 'success' : 'info',
                route('aprendiz.dashboard')
            );
        }

        $this->auditService->log(
            'Bienestar · Trazabilidad de Casos',
            "Actualizó caso [{$updatedCase->case_code}] a estado '{$updatedCase->status}'",
            'info',
            'WelfareCase',
            $updatedCase->id,
            $oldValues,
            $updatedCase->only(['status', 'priority', 'intervention_route', 'commitments', 'improvement_plan', 'resolution_notes'])
        );

        return $updatedCase;
    }

    /**
     * Regla de negocio inquebrantable: bloquea la eliminación de un caso hasta su resolución.
     */
    public function deleteCaseIfResolved(int $caseId): void
    {
        $case = $this->welfareCaseRepository->findById($caseId);
        if (!$case) {
            throw new DomainException('Caso no encontrado.');
        }

        if (!$case->canBeDeleted()) {
            $this->auditService->log(
                'Bienestar · Trazabilidad de Casos',
                "Intento bloqueado de eliminar caso activo [{$case->case_code}] en estado '{$case->status}'",
                'critical',
                'WelfareCase',
                $case->id,
                ['status' => $case->status],
                null
            );

            throw new DomainException(
                "Operación Bloqueada: El caso [{$case->case_code}] se encuentra en estado '{$case->statusLabel()}'. La política de trazabilidad prohíbe eliminar casos hasta que estén formalmente RESUELTOS."
            );
        }

        $snapshot = $case->only(['case_code', 'title', 'status', 'resolved_at']);
        $this->welfareCaseRepository->deleteResolvedCase($caseId);

        $this->auditService->log(
            'Bienestar · Trazabilidad de Casos',
            "Eliminó/archivó caso resuelto [{$snapshot['case_code']}]",
            'warning',
            'WelfareCase',
            $caseId,
            $snapshot,
            null
        );
    }

    /**
     * Genera reportes masivos consolidados de vulnerabilidad por ficha, programa o jornada.
     */
    public function generateMassiveVulnerabilityReport(array $filters = []): array
    {
        $apprentices = $this->apprenticeRepository->getAllWithRelations($filters);
        $stats = $this->apprenticeRepository->getSemaphoreStats($filters);

        $byFicha = $apprentices->groupBy(fn ($a) => $a->ficha?->code ?? 'Sin Ficha')->map(function (Collection $group, string $fichaCode) {
            $first = $group->first();
            return [
                'ficha_code' => $fichaCode,
                'program' => $first?->ficha?->trainingProgram?->name ?? 'N/A',
                'jornada' => $first?->ficha?->jornada ?? 'N/A',
                'sede' => $first?->ficha?->sede?->name ?? 'N/A',
                'total' => $group->count(),
                'alto' => $group->where('risk_level', 'alto')->count(),
                'medio' => $group->where('risk_level', 'medio')->count(),
                'bajo' => $group->where('risk_level', 'bajo')->count(),
                'avg_global' => round($group->avg('global_risk_score'), 1),
                'avg_dropout' => round($group->avg('dropout_risk_score'), 1),
                'avg_academic' => round($group->avg('academic_risk_score'), 1),
                'avg_psychosocial' => round($group->avg('psychosocial_risk_score'), 1),
            ];
        })->values();

        $byJornada = $apprentices->groupBy(fn ($a) => $a->ficha?->jornada ?? 'No Asignada')->map(function (Collection $group, string $jornada) {
            return [
                'jornada' => $jornada,
                'total' => $group->count(),
                'alto' => $group->where('risk_level', 'alto')->count(),
                'medio' => $group->where('risk_level', 'medio')->count(),
                'bajo' => $group->where('risk_level', 'bajo')->count(),
                'avg_global' => round($group->avg('global_risk_score'), 1),
            ];
        })->values();

        return [
            'apprentices' => $apprentices,
            'stats' => $stats,
            'by_ficha' => $byFicha,
            'by_jornada' => $byJornada,
        ];
    }
}
