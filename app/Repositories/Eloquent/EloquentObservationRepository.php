<?php

namespace App\Repositories\Eloquent;

use App\Models\EvidenceSupport;
use App\Models\Observation;
use App\Repositories\Contracts\ObservationRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentObservationRepository implements ObservationRepositoryInterface
{
    public function getForFichaIds(array $fichaIds, int $limit = 100): Collection
    {
        return Observation::with([
            'apprenticeProfile.user',
            'ficha.trainingProgram',
            'author',
            'supports.reviewer',
            'welfareCase',
        ])
            ->whereIn('ficha_id', $fichaIds)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getForApprentice(int $apprenticeProfileId): Collection
    {
        return Observation::with([
            'ficha.trainingProgram',
            'author',
            'supports.reviewer',
            'welfareCase',
        ])
            ->where('apprentice_profile_id', $apprenticeProfileId)
            ->latest()
            ->get();
    }

    public function findById(int $id): ?Observation
    {
        return Observation::with(['apprenticeProfile.user', 'ficha', 'author', 'supports', 'welfareCase'])->find($id);
    }

    public function create(array $data): Observation
    {
        return Observation::create($data);
    }

    public function markEscalated(int $observationId): Observation
    {
        $observation = Observation::findOrFail($observationId);
        $observation->update([
            'escalated_to_welfare' => true,
            'escalated_at' => now(),
        ]);

        return $observation->fresh();
    }

    public function createSupport(array $data): EvidenceSupport
    {
        return EvidenceSupport::create($data);
    }

    public function findSupportById(int $supportId): ?EvidenceSupport
    {
        return EvidenceSupport::with(['observation.apprenticeProfile.user', 'apprenticeProfile.user', 'reviewer'])->find($supportId);
    }

    public function updateSupportStatus(int $supportId, string $status, int $reviewedBy, ?string $notes = null): EvidenceSupport
    {
        $support = EvidenceSupport::findOrFail($supportId);
        $support->update([
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'review_notes' => $notes,
            'reviewed_at' => now(),
        ]);

        if ($status === 'aprobado') {
            $support->observation()->update(['mitigated_by_support' => true]);
        } else {
            $hasOtherApproved = $support->observation->supports()->where('status', 'aprobado')->exists();
            $support->observation()->update(['mitigated_by_support' => $hasOtherApproved]);
        }

        return $support->fresh(['observation', 'apprenticeProfile.user', 'reviewer']);
    }
}
