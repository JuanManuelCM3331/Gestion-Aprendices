<?php

namespace App\Services;

use App\Models\ApprenticeProfile;
use App\Repositories\Contracts\ApprenticeRepositoryInterface;
use App\Repositories\Contracts\RiskParameterRepositoryInterface;

class RiskEngineService
{
    public function __construct(
        private readonly ApprenticeRepositoryInterface $apprenticeRepository,
        private readonly RiskParameterRepositoryInterface $parameterRepository,
        private readonly NotificationService $notificationService,
        private readonly AuditService $auditService
    ) {}

    /**
     * Evalúa y actualiza en tiempo real los 3 vectores de riesgo (deserción, académico, psicosocial)
     * y la semaforización automática del aprendiz según los parámetros activos en base de datos.
     */
    public function recalculateForApprentice(int $apprenticeProfileId, bool $notifyOnChange = true): ApprenticeProfile
    {
        $profile = $this->apprenticeRepository->findByIdWithContext($apprenticeProfileId);
        if (!$profile) {
            throw new \InvalidArgumentException("Aprendiz ID {$apprenticeProfileId} no encontrado.");
        }

        $previousLevel = $profile->risk_level;
        $weights = $this->parameterRepository->getKeyedWeights();

        // Pesos dinámicos configurables por Bienestar
        $wAbsence = $weights['peso_inasistencias'] ?? 8.5;
        $wLowAttendance = $weights['peso_baja_asistencia'] ?? 1.8;
        $wLowGrade = $weights['peso_promedio_bajo'] ?? 22.0;
        $wAcademicObs = $weights['peso_novedad_academica'] ?? 6.0;
        $wWarning = $weights['peso_llamado_atencion'] ?? 12.0;
        $wPsychoVuln = $weights['peso_vulnerabilidad_psico'] ?? 14.0;
        $wPositiveBonus = $weights['bono_observacion_positiva'] ?? 5.0;

        $thresholdMedium = $weights['umbral_riesgo_medio'] ?? 35.0;
        $thresholdHigh = $weights['umbral_riesgo_alto'] ?? 65.0;

        $observations = $profile->observations;

        // Filtrar observaciones no mitigadas por una excusa médica/soporte aprobado
        $activeObservations = $observations->where('mitigated_by_support', false);

        $absencesObsCount = $activeObservations->where('type', 'inasistencia')->count();
        $academicObsSeverity = $activeObservations->where('type', 'academica')->sum('severity');
        $disciplinaryObsSeverity = $activeObservations->whereIn('type', ['disciplinaria', 'llamado_atencion'])->sum('severity');
        $psychosocialObsSeverity = $activeObservations->where('category', 'psicosocial')->sum('severity');
        $positiveObsCount = $observations->where('type', 'positiva')->count();

        // 1. Cálculo de Riesgo de Deserción (0 - 100)
        $attendanceDeficit = max(0.0, 92.0 - (float) $profile->attendance_rate);
        $totalAbsences = max((int) $profile->absences_count, $absencesObsCount);
        $connectivityPenalty = $profile->has_connectivity_issues ? ($wPsychoVuln * 0.8) : 0.0;

        $dropoutScore = ($totalAbsences * $wAbsence)
            + ($attendanceDeficit * $wLowAttendance)
            + $connectivityPenalty
            - ($positiveObsCount * ($wPositiveBonus * 0.5));
        $dropoutScore = round(max(0.0, min(100.0, $dropoutScore)), 2);

        // 2. Cálculo de Riesgo Académico (0 - 100)
        $gradeDeficit = max(0.0, 3.8 - (float) $profile->academic_average);
        $academicScore = ($gradeDeficit * $wLowGrade)
            + ($academicObsSeverity * $wAcademicObs)
            + ($disciplinaryObsSeverity * ($wWarning * 0.45))
            - ($positiveObsCount * $wPositiveBonus);
        $academicScore = round(max(0.0, min(100.0, $academicScore)), 2);

        // 3. Cálculo de Riesgo Psicosocial (0 - 100)
        $familyPenalty = $profile->has_family_vulnerability ? ($wPsychoVuln * 1.5) : 0.0;
        $stratumFactor = in_array((string) $profile->socioeconomic_stratum, ['1', '2'], true) ? ($wPsychoVuln * 0.6) : 0.0;
        $escalatedCount = $activeObservations->where('escalated_to_welfare', true)->count();

        $psychosocialScore = $familyPenalty
            + $stratumFactor
            + ($psychosocialObsSeverity * ($wPsychoVuln * 0.55))
            + ($escalatedCount * ($wWarning * 1.1))
            - ($positiveObsCount * ($wPositiveBonus * 0.4));
        $psychosocialScore = round(max(0.0, min(100.0, $psychosocialScore)), 2);

        // 4. Puntaje Global Ponderado y Semáforo Automático
        $globalScore = round(
            ($dropoutScore * 0.40) + ($academicScore * 0.35) + ($psychosocialScore * 0.25),
            2
        );
        // Si alguno de los vectores específicos es extremo, empuja el riesgo global
        $maxVector = max($dropoutScore, $academicScore, $psychosocialScore);
        if ($maxVector >= $thresholdHigh && $globalScore < $thresholdHigh) {
            $globalScore = round(($globalScore + $maxVector) / 2, 2);
        }

        $riskLevel = 'bajo';
        if ($globalScore >= $thresholdHigh || $maxVector >= ($thresholdHigh + 10)) {
            $riskLevel = 'alto';
        } elseif ($globalScore >= $thresholdMedium || $maxVector >= $thresholdHigh) {
            $riskLevel = 'medio';
        }

        $academicStatus = match ($riskLevel) {
            'alto' => 'Condicionado',
            'medio' => 'En Plan de Mejora',
            default => 'En Formación',
        };

        $updated = $this->apprenticeRepository->updateRiskScores($profile->id, [
            'dropout_risk_score' => $dropoutScore,
            'academic_risk_score' => $academicScore,
            'psychosocial_risk_score' => $psychosocialScore,
            'global_risk_score' => $globalScore,
            'risk_level' => $riskLevel,
            'academic_status' => $academicStatus,
        ]);

        if ($notifyOnChange && $previousLevel !== $riskLevel && $profile->user_id) {
            $severity = $riskLevel === 'alto' ? 'danger' : ($riskLevel === 'medio' ? 'warning' : 'success');
            $this->notificationService->notifyUser(
                $profile->user_id,
                'Actualización en tu Semáforo de Riesgo: ' . strtoupper($riskLevel),
                "Tu nivel de valoración integral cambió de " . strtoupper($previousLevel) . " a " . strtoupper($riskLevel) . " (Puntaje global: {$globalScore}%). Revisa tu expediente y compromisos.",
                'riesgo',
                $severity,
                route('aprendiz.dashboard')
            );
        }

        return $updated;
    }

    /**
     * Recalcula masivamente el riesgo de todos los aprendices registrados cuando Bienestar ajusta pesos.
     */
    public function recalculateAllApprentices(bool $notifyOnChange = false): int
    {
        $apprentices = $this->apprenticeRepository->getAllWithRelations();
        foreach ($apprentices as $apprentice) {
            $this->recalculateForApprentice($apprentice->id, $notifyOnChange);
        }

        return $apprentices->count();
    }
}
