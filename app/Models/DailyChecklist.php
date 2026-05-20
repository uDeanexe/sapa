<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyChecklist extends Model
{
    protected $fillable = [
        'user_id',
        'answers',
        'date',
        'tipe_form',
    ];

    protected $casts = [
        'answers' => 'array',
        'date' => 'date',
    ];
}
