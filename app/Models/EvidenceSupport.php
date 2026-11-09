<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceSupport extends Model
{
    protected $fillable = [
        'observation_id',
        'apprentice_profile_id',
        'title',
        'support_type',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'status',
        'reviewed_by',
        'review_notes',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    public function apprenticeProfile(): BelongsTo
    {
        return $this->belongsTo(ApprenticeProfile::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
