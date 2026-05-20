<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RecruitmentCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'recruitment_opening_id',
        'name',
        'phone',
        'position',
        'source',
        'stage',
        'score',
        'screening_notes',
        'cv_path',
    ];

    public function opening(): BelongsTo
    {
        return $this->belongsTo(RecruitmentOpening::class, 'recruitment_opening_id');
    }

    public function getCvUrlAttribute(): ?string
    {
        if (!$this->cv_path) {
            return null;
        }

        return Storage::disk('public')->url($this->cv_path);
    }
}
