<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Presence extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'category',
        'check_in',
        'check_out',
        'lat_in',
        'lng_in',
        'lat_out',
        'lng_out',
        'photo_in',
        'photo_out',
        'notes',
        'notes_out',
        'is_approved',
        'is_approved_out',
    ];

    protected $casts = [
        'date' => 'date',
        'lat_in' => 'float',
        'lng_in' => 'float',
        'lat_out' => 'float',
        'lng_out' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeCheckInRecords($query)
    {
        return $query->whereNotNull('check_in');
    }

    public function getPhotoInUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->photo_in);
    }

    public function getPhotoOutUrlAttribute(): ?string
    {
        return $this->publicFileUrl($this->photo_out);
    }

    private function publicFileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        $path = str_starts_with($path, 'storage/') ? $path : 'storage/'.$path;

        return asset($path);
    }
}
