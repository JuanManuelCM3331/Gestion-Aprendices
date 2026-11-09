<?php

namespace App\Repositories\Eloquent;

use App\Models\WelfareCase;
use App\Models\WelfareCaseFollowup;
use App\Repositories\Contracts\WelfareCaseRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentWelfareCaseRepository implements WelfareCaseRepositoryInterface
{
    public function getAllWithRelations(array $filters = []): Collection
    {
        $query = WelfareCase::with([
            'apprenticeProfile.user',
            'apprenticeProfile.ficha.trainingProgram',
            'apprenticeProfile.ficha.sede',
            'originObservation',
            'opener',
            'assignee',
            'followups.author',
        ]);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['risk_type'])) {
            $query->where('risk_type', $filters['risk_type']);
        }

        return $query->latest()->get();
    }

    public function findById(int $id): ?WelfareCase
    {
        return WelfareCase::with([
            'apprenticeProfile.user',
            'apprenticeProfile.ficha.trainingProgram',
            'originObservation',
            'opener',
            'assignee',
            'followups.author',
        ])->find($id);
    }

    public function createCase(array $data): WelfareCase
    {
        return WelfareCase::create($data);
    }

    public function updateCase(int $id, array $data): WelfareCase
    {
        $case = WelfareCase::findOrFail($id);
        $case->update($data);
        return $case->fresh(['apprenticeProfile.user', 'followups.author']);
    }

    public function addFollowup(int $caseId, array $data): WelfareCaseFollowup
    {
        return WelfareCaseFollowup::create(array_merge($data, [
            'welfare_case_id' => $caseId,
        ]));
    }

    public function deleteResolvedCase(int $id): bool
    {
        $case = WelfareCase::findOrFail($id);
        return (bool) $case->delete();
    }
}
