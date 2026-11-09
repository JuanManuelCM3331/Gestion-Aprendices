<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskParameter extends Model
{
    protected $fillable = [
        'code',
        'name',
        'dimension',
        'weight',
        'min_value',
        'max_value',
        'description',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'min_value' => 'float',
            'max_value' => 'float',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
