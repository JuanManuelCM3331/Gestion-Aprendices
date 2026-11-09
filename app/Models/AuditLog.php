<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'username',
        'role',
        'ip_address',
        'user_agent',
        'module',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'severity',
        'previous_hash',
        'integrity_hash',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new DomainException('Seguridad e Inmutabilidad: Los registros de auditoría son inmutables y no admiten modificaciones.');
        });

        static::deleting(function () {
            throw new DomainException('Seguridad e Inmutabilidad: Los registros de auditoría son inmutables y no pueden ser eliminados.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
