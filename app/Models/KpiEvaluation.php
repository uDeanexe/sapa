<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_name',
        'division',
        'period',
        'score',
        'grade',
        'status',
        'note',
    ];

    protected static function booted(): void
    {
        static::saving(function (KpiEvaluation $evaluation) {
            $evaluation->grade = self::gradeFromScore((int) $evaluation->score);
        });
    }

    public static function gradeFromScore(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            default => 'D',
        };
    }
}
