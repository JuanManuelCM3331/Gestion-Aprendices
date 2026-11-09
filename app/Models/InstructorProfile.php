<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InstructorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'document_number',
        'specialty',
        'contract_type',
        'weekly_hours',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fichas(): BelongsToMany
    {
        return $this->belongsToMany(Ficha::class, 'ficha_instructor')
            ->withPivot(['id', 'competency', 'schedule', 'hours_assigned', 'is_lead'])
            ->withTimestamps();
    }
}
