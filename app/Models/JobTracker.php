<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobTracker extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'step_number',
        'description_value',
        'photo_path',
        'video_path'
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function getPublicPhotoUrlAttribute(): ?string
    {
        return $this->publicStorageUrl($this->photo_path);
    }

    public function getPublicVideoUrlAttribute(): ?string
    {
        return $this->publicStorageUrl($this->video_path);
    }

    private function publicStorageUrl(?string $path): ?string
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        if (Str::startsWith($path, 'public/')) {
            return asset('storage/'.Str::after($path, 'public/'));
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
