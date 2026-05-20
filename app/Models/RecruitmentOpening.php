<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruitmentOpening extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'division',
        'employment_type',
        'quota',
        'status',
        'priority',
        'sla',
        'description',
        'criteria',
    ];

    public function candidates(): HasMany
    {
        return $this->hasMany(RecruitmentCandidate::class);
    }
}
