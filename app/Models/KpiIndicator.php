<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiIndicator extends Model
{
    use HasFactory;

    public const MEASUREMENT_METHODS = [
        'Task',
        'Absensi',
        'Checklist',
        'Feedback',
        'Chat',
        'Manual',
    ];

    protected $fillable = [
        'area',
        'indicator',
        'weight',
        'target',
        'measurement_method',
        'is_locked',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function lock(): bool
    {
        return $this->update(['is_locked' => true]);
    }
}
