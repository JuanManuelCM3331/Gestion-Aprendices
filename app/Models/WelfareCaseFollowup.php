<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelfareCaseFollowup extends Model
{
    protected $fillable = [
        'welfare_case_id',
        'user_id',
        'stage',
        'notes',
        'next_followup_date',
    ];

    protected function casts(): array
    {
        return [
            'next_followup_date' => 'date',
        ];
    }

    public function welfareCase(): BelongsTo
    {
        return $this->belongsTo(WelfareCase::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
