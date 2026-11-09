<?php

namespace App\Repositories\Contracts;

use App\Models\WelfareCase;
use App\Models\WelfareCaseFollowup;
use Illuminate\Support\Collection;

interface WelfareCaseRepositoryInterface
{
    public function getAllWithRelations(array $filters = []): Collection;
    public function findById(int $id): ?WelfareCase;
    public function createCase(array $data): WelfareCase;
    public function updateCase(int $id, array $data): WelfareCase;
    public function addFollowup(int $caseId, array $data): WelfareCaseFollowup;
    public function deleteResolvedCase(int $id): bool;
}
