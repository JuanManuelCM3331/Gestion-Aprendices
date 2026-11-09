<?php

namespace App\Repositories\Contracts;

use App\Models\ApprenticeProfile;
use Illuminate\Support\Collection;

interface ApprenticeRepositoryInterface
{
    public function getAllWithRelations(array $filters = []): Collection;
    public function findByIdWithContext(int $id): ?ApprenticeProfile;
    public function findByUserId(int $userId): ?ApprenticeProfile;
    public function getByFichaIds(array $fichaIds, array $filters = []): Collection;
    public function updateRiskScores(int $apprenticeProfileId, array $scores): ApprenticeProfile;
    public function updateAcademicMetrics(int $apprenticeProfileId, array $metrics): ApprenticeProfile;
    public function getSemaphoreStats(array $filters = []): array;
}
