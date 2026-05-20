<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class ChatCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Chat $chat)
    {
        //
    }

    public function broadcastOn(): Channel
    {
        if (! Schema::hasTable('chat_recipients')) {
            return new Channel('chat');
        }

        $chat = $this->chat->loadMissing('recipients:id');
        $recipientIds = $chat->recipients->pluck('id')->map(fn ($id) => (int) $id)->values();

        if ($recipientIds->isEmpty()) {
            return new Channel('chat');
        }

        $userIds = $recipientIds->push((int) $chat->user_id)->unique()->values();

        return $userIds
            ->map(fn (int $id) => new PrivateChannel('chat.user.'.$id))
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'chat.created';
    }

    public function broadcastWith(): array
    {
        $chat = $this->chat->loadMissing('user:id,name');

        $preview = trim((string) ($chat->message ?? ''));
        if ($preview === '') {
            $preview = match ($chat->type) {
                'image' => 'Mengirim foto',
                'video' => 'Mengirim video',
                'audio', 'voice' => 'Mengirim audio',
                'file' => 'Mengirim file',
                default => 'Pesan baru',
            };
        }

        return [
            'id' => $chat->id,
            'sender' => $chat->user?->name ?? 'Unknown',
            'preview' => mb_substr($preview, 0, 160),
            'type' => $chat->type,
            'created_at' => $chat->created_at?->toIso8601String(),
        ];
    }
}
