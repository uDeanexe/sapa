<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['division_id', 'tipe_form', 'questions'];

    protected $casts = [
        'questions' => 'array',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}