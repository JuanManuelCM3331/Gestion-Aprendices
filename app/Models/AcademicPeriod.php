<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'start_date',
        'end_date',
        'is_current',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function fichas(): HasMany
    {
        return $this->hasMany(Ficha::class);
    }
}
