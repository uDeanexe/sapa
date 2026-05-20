<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Chat extends Model
{
    protected $fillable = [
        'user_id', 'message', 'type', 'file_path',
        'parent_id', 'is_pinned', 'is_edited',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_edited' => 'boolean',
    ];

    protected $appends = [
        'public_file_path',
        'public_file_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'parent_id');
    }

    public function seenBy(): HasMany
    {
        return $this->hasMany(ChatSeen::class);
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_recipients', 'chat_id', 'user_id')
            ->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        if (! Schema::hasTable('chat_recipients')) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($userId) {
            $q->whereDoesntHave('recipients')
                ->orWhere('user_id', $userId)
                ->orWhereHas('recipients', function (Builder $r) use ($userId) {
                    $r->where('users.id', $userId);
                });
        });
    }

    public function getPublicFilePathAttribute(): ?string
    {
        if (! $this->file_path || Str::startsWith($this->file_path, ['http://', 'https://'])) {
            return $this->file_path;
        }

        if ($this->isEncryptedVideo()) {
            return basename(Str::beforeLast($this->file_path, '.enc'));
        }

        if (Str::startsWith($this->file_path, 'public/')) {
            return 'storage/'.Str::after($this->file_path, 'public/');
        }

        if (Str::startsWith($this->file_path, 'storage/')) {
            return $this->file_path;
        }

        if (Str::startsWith($this->file_path, 'chat-uploads/')) {
            return basename($this->file_path);
        }

        return 'storage/'.ltrim($this->file_path, '/');
    }

    public function getPublicFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        if ($this->isEncryptedVideo()) {
            return route('chat.media.show', $this);
        }

        if (Str::startsWith($this->file_path, 'chat-uploads/')) {
            return route('chat.media.show', $this);
        }

        if (Str::startsWith($this->file_path, ['http://', 'https://'])) {
            return $this->file_path;
        }

        if (file_exists(public_path($this->file_path))) {
            return asset($this->file_path);
        }

        return asset($this->public_file_path);
    }

    public function isEncryptedVideo(): bool
    {
        return $this->type === 'video'
            && $this->file_path
            && Str::startsWith($this->file_path, 'encrypted-videos/')
            && Str::endsWith($this->file_path, '.enc');
    }
}
