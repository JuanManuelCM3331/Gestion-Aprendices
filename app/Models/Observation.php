<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Observation extends Model
{
    protected $fillable = [
        'apprentice_profile_id',
        'ficha_id',
        'author_id',
        'type',
        'category',
        'severity',
        'title',
        'description',
        'is_massive',
        'escalated_to_welfare',
        'escalated_at',
        'mitigated_by_support',
    ];

    protected function casts(): array
    {
        return [
            'is_massive' => 'boolean',
            'escalated_to_welfare' => 'boolean',
            'mitigated_by_support' => 'boolean',
            'escalated_at' => 'datetime',
        ];
    }

    public function apprenticeProfile(): BelongsTo
    {
        return $this->belongsTo(ApprenticeProfile::class);
    }

    public function ficha(): BelongsTo
    {
        return $this->belongsTo(Ficha::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function supports(): HasMany
    {
        return $this->hasMany(EvidenceSupport::class)->latest();
    }

    public function welfareCase(): HasOne
    {
        return $this->hasOne(WelfareCase::class, 'origin_observation_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'positiva' => 'Observación Positiva / Mérito',
            'academica' => 'Novedad Académica',
            'disciplinaria' => 'Observación Disciplinaria',
            'inasistencia' => 'Inasistencia Injustificada',
            'llamado_atencion' => 'Llamado de Atención Formal',
            default => ucfirst($this->type),
        };
    }
}
