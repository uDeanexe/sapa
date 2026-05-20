<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'period',
        'division',
        'start_date',
        'end_date',
        'status',
        'progress',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
