<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprenticeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'ficha_id',
        'document_type',
        'document_number',
        'phone',
        'attendance_rate',
        'academic_average',
        'absences_count',
        'socioeconomic_stratum',
        'has_connectivity_issues',
        'has_family_vulnerability',
        'dropout_risk_score',
        'academic_risk_score',
        'psychosocial_risk_score',
        'global_risk_score',
        'risk_level',
        'academic_status',
        'last_risk_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'attendance_rate' => 'float',
            'academic_average' => 'float',
            'dropout_risk_score' => 'float',
            'academic_risk_score' => 'float',
            'psychosocial_risk_score' => 'float',
            'global_risk_score' => 'float',
            'has_connectivity_issues' => 'boolean',
            'has_family_vulnerability' => 'boolean',
            'last_risk_calculated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ficha(): BelongsTo
    {
        return $this->belongsTo(Ficha::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class)->latest();
    }

    public function evidenceSupports(): HasMany
    {
        return $this->hasMany(EvidenceSupport::class)->latest();
    }

    public function welfareCases(): HasMany
    {
        return $this->hasMany(WelfareCase::class)->latest();
    }

    public function semaphoreColor(): string
    {
        return match ($this->risk_level) {
            'alto' => 'rose',
            'medio' => 'amber',
            default => 'emerald',
        };
    }

    public function semaphoreLabel(): string
    {
        return match ($this->risk_level) {
            'alto' => 'Riesgo Alto / Crítico',
            'medio' => 'Riesgo Medio / Alerta',
            default => 'Riesgo Bajo / Estable',
        };
    }
}
