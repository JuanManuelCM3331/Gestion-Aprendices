<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditRepository
    ) {}

    public function log(
        string $module,
        string $action,
        string $severity = 'info',
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $user = Auth::user();
        $lastLog = $this->auditRepository->getLastLog();
        $previousHash = $lastLog?->integrity_hash ?? str_repeat('0', 64);
        $timestamp = now()->toIso8601String();
        $ipAddress = Request::ip() ?? '127.0.0.1';
        $userAgent = substr((string) Request::userAgent(), 0, 250);
        $username = $user?->username ?? 'sistema';
        $role = $user?->role ?? 'system';

        $payloadToSign = implode('|', [
            $previousHash,
            $username,
            $role,
            $ipAddress,
            $module,
            $action,
            $severity,
            json_encode($oldValues ?? []),
            json_encode($newValues ?? []),
            $timestamp,
        ]);

        $integrityHash = hash('sha256', $payloadToSign);

        return $this->auditRepository->record([
            'user_id' => $user?->id,
            'username' => $username,
            'role' => $role,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'module' => $module,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'severity' => $severity,
            'previous_hash' => $previousHash,
            'integrity_hash' => $integrityHash,
            'created_at' => now(),
        ]);
    }

    public function getLogs(array $filters = [], int $limit = 150): Collection
    {
        return $this->auditRepository->getLatest($limit, $filters);
    }

    public function verifyChainIntegrity(): array
    {
        $logs = AuditLog::query()->orderBy('id')->get();
        $expectedPrev = str_repeat('0', 64);

        foreach ($logs as $log) {
            if ($log->previous_hash !== $expectedPrev) {
                return [
                    'valid' => false,
                    'checked_count' => $logs->count(),
                    'broken_at_id' => $log->id,
                ];
            }
            $expectedPrev = $log->integrity_hash;
        }

        return [
            'valid' => true,
            'checked_count' => $logs->count(),
            'last_hash' => $expectedPrev,
        ];
    }
}
