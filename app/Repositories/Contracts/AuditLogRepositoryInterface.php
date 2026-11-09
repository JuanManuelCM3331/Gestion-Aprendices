<?php

namespace App\Repositories\Contracts;

use App\Models\AuditLog;
use Illuminate\Support\Collection;

interface AuditLogRepositoryInterface
{
    public function getLatest(int $limit = 150, array $filters = []): Collection;
    public function getLastLog(): ?AuditLog;
    public function record(array $data): AuditLog;
}
