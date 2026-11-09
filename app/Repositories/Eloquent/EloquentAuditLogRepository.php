<?php

namespace App\Repositories\Eloquent;

use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function getLatest(int $limit = 150, array $filters = []): Collection
    {
        $query = AuditLog::query()->with('user');

        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (!empty($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $query->where(function ($sub) use ($term) {
                $sub->where('username', 'like', $term)
                    ->orWhere('action', 'like', $term)
                    ->orWhere('ip_address', 'like', $term);
            });
        }

        return $query->orderByDesc('id')->limit($limit)->get();
    }

    public function getLastLog(): ?AuditLog
    {
        return AuditLog::query()->orderByDesc('id')->first();
    }

    public function record(array $data): AuditLog
    {
        return AuditLog::create($data);
    }
}
