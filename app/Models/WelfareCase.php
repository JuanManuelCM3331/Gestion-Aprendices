<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WelfareCase extends Model
{
    protected $fillable = [
        'case_code',
        'apprentice_profile_id',
        'origin_observation_id',
        'opened_by',
        'assigned_to',
        'risk_type',
        'priority',
        'status',
        'title',
        'initial_diagnosis',
        'intervention_route',
        'commitments',
        'improvement_plan',
        'resolution_notes',
        'due_date',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (WelfareCase $case) {
            if ($case->status !== 'resuelto') {
                throw new DomainException(
                    "Bloqueo de Trazabilidad: El caso [{$case->case_code}] no puede eliminarse porque su estado actual es '{$case->statusLabel()}'. Solo los casos resueltos pueden ser eliminados."
                );
            }
        });
    }

    public function apprenticeProfile(): BelongsTo
    {
        return $this->belongsTo(ApprenticeProfile::class);
    }

    public function originObservation(): BelongsTo
    {
        return $this->belongsTo(Observation::class, 'origin_observation_id');
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(WelfareCaseFollowup::class)->latest();
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'resuelto';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'abierto' => 'Abierto / Sin Ruta',
            'en_ruta' => 'En Ruta de Atención',
            'compromiso_activo' => 'Con Compromiso Firmado',
            'en_seguimiento' => 'En Seguimiento Activo',
            'resuelto' => 'Resuelto / Cerrado',
            default => ucfirst($this->status),
        };
    }
}
