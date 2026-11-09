<?php

namespace App\Repositories\Eloquent;

use App\Models\ApprenticeProfile;
use App\Repositories\Contracts\ApprenticeRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentApprenticeRepository implements ApprenticeRepositoryInterface
{
    public function getAllWithRelations(array $filters = []): Collection
    {
        $query = ApprenticeProfile::query()
            ->with([
                'user',
                'ficha.trainingProgram',
                'ficha.sede',
                'ficha.academicPeriod',
                'observations.author',
                'observations.supports',
                'welfareCases.followups',
            ]);

        if (!empty($filters['ficha_id'])) {
            $query->where('ficha_id', $filters['ficha_id']);
        }

        if (!empty($filters['program_id'])) {
            $query->whereHas('ficha', fn ($q) => $q->where('training_program_id', $filters['program_id']));
        }

        if (!empty($filters['jornada'])) {
            $query->whereHas('ficha', fn ($q) => $q->where('jornada', $filters['jornada']));
        }

        if (!empty($filters['sede_id'])) {
            $query->whereHas('ficha', fn ($q) => $q->where('sede_id', $filters['sede_id']));
        }

        if (!empty($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $query->where(function ($sub) use ($term) {
                $sub->where('document_number', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('username', 'like', $term));
            });
        }

        return $query->orderByDesc('global_risk_score')->get();
    }

    public function findByIdWithContext(int $id): ?ApprenticeProfile
    {
        return ApprenticeProfile::with([
            'user',
            'ficha.trainingProgram',
            'ficha.sede',
            'ficha.academicPeriod',
            'ficha.instructors.user',
            'observations.author',
            'observations.supports.reviewer',
            'welfareCases.opener',
            'welfareCases.assignee',
            'welfareCases.followups.author',
        ])->find($id);
    }

    public function findByUserId(int $userId): ?ApprenticeProfile
    {
        return ApprenticeProfile::with([
            'user',
            'ficha.trainingProgram',
            'ficha.sede',
            'ficha.academicPeriod',
            'ficha.instructors.user',
            'observations.author',
            'observations.supports.reviewer',
            'welfareCases.opener',
            'welfareCases.assignee',
            'welfareCases.followups.author',
        ])->where('user_id', $userId)->first();
    }

    public function getByFichaIds(array $fichaIds, array $filters = []): Collection
    {
        $query = ApprenticeProfile::with([
            'user',
            'ficha.trainingProgram',
            'ficha.sede',
            'observations.author',
            'observations.supports',
            'welfareCases',
        ])->whereIn('ficha_id', $fichaIds);

        if (!empty($filters['ficha_id'])) {
            $query->where('ficha_id', $filters['ficha_id']);
        }

        if (!empty($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        return $query->orderByDesc('global_risk_score')->get();
    }

    public function updateRiskScores(int $apprenticeProfileId, array $scores): ApprenticeProfile
    {
        $profile = ApprenticeProfile::findOrFail($apprenticeProfileId);
        $profile->update([
            'dropout_risk_score' => $scores['dropout_risk_score'],
            'academic_risk_score' => $scores['academic_risk_score'],
            'psychosocial_risk_score' => $scores['psychosocial_risk_score'],
            'global_risk_score' => $scores['global_risk_score'],
            'risk_level' => $scores['risk_level'],
            'academic_status' => $scores['academic_status'] ?? $profile->academic_status,
            'last_risk_calculated_at' => now(),
        ]);

        return $profile->fresh();
    }

    public function updateAcademicMetrics(int $apprenticeProfileId, array $metrics): ApprenticeProfile
    {
        $profile = ApprenticeProfile::findOrFail($apprenticeProfileId);
        $profile->update($metrics);
        return $profile->fresh();
    }

    public function getSemaphoreStats(array $filters = []): array
    {
        $collection = $this->getAllWithRelations($filters);

        $total = $collection->count();
        $alto = $collection->where('risk_level', 'alto')->count();
        $medio = $collection->where('risk_level', 'medio')->count();
        $bajo = $collection->where('risk_level', 'bajo')->count();

        return [
            'total' => $total,
            'alto' => $alto,
            'medio' => $medio,
            'bajo' => $bajo,
            'avg_dropout' => $total > 0 ? round($collection->avg('dropout_risk_score'), 1) : 0,
            'avg_academic' => $total > 0 ? round($collection->avg('academic_risk_score'), 1) : 0,
            'avg_psychosocial' => $total > 0 ? round($collection->avg('psychosocial_risk_score'), 1) : 0,
            'avg_global' => $total > 0 ? round($collection->avg('global_risk_score'), 1) : 0,
        ];
    }
}
