<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ficha extends Model
{
    protected $fillable = [
        'code',
        'training_program_id',
        'sede_id',
        'academic_period_id',
        'jornada',
        'classroom',
        'status',
    ];

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function apprentices(): HasMany
    {
        return $this->hasMany(ApprenticeProfile::class);
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(InstructorProfile::class, 'ficha_instructor')
            ->withPivot(['id', 'competency', 'schedule', 'hours_assigned', 'is_lead'])
            ->withTimestamps();
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }
}
