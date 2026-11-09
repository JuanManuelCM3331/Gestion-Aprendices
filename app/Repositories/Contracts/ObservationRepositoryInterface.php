<?php

namespace App\Repositories\Contracts;

use App\Models\EvidenceSupport;
use App\Models\Observation;
use Illuminate\Support\Collection;

interface ObservationRepositoryInterface
{
    public function getForFichaIds(array $fichaIds, int $limit = 100): Collection;
    public function getForApprentice(int $apprenticeProfileId): Collection;
    public function findById(int $id): ?Observation;
    public function create(array $data): Observation;
    public function markEscalated(int $observationId): Observation;
    public function createSupport(array $data): EvidenceSupport;
    public function findSupportById(int $supportId): ?EvidenceSupport;
    public function updateSupportStatus(int $supportId, string $status, int $reviewedBy, ?string $notes = null): EvidenceSupport;
}
