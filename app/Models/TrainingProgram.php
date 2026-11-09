<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    protected $fillable = [
        'code',
        'name',
        'level',
        'duration_trimesters',
        'area',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function fichas(): HasMany
    {
        return $this->hasMany(Ficha::class);
    }
}
