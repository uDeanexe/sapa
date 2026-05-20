<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ChatWebController extends Controller {
    public function index() {
        $currentUserId = (int) auth()->id();
        $messagesQuery = Chat::query()
            ->with(['user', 'parent.user', 'seenBy']);

        if (Schema::hasTable('chat_recipients')) {
            $messagesQuery->with('recipients:id,name');
        }

        $messages = $messagesQuery
            ->visibleTo($currentUserId)
            ->orderBy('created_at', 'asc')
            ->get();
        $scrollToLatest = true;

        return view('admin.chat.chat', compact('messages', 'scrollToLatest'));
    }

    public function poll(Request $request): JsonResponse
    {
        $currentUserId = (int) auth()->id();
        $baseQuery = Chat::query()->visibleTo($currentUserId);

        $latestId = (int) ((clone $baseQuery)->max('id') ?? 0);
        $latestUpdatedAt = (clone $baseQuery)->max('updated_at');
        $pinnedCount = (clone $baseQuery)->where('is_pinned', true)->count();
        $totalCount = (clone $baseQuery)->count();

        $signature = hash(
            'sha256',
            implode('|', [
                $latestId,
                $latestUpdatedAt ? strtotime((string) $latestUpdatedAt) : 0,
                $pinnedCount,
                $totalCount,
            ])
        );

        $sinceId = (int) $request->query('since_id', 0);

        $newMessagesQuery = Chat::query()
            ->with('user:id,name');

        if (Schema::hasTable('chat_recipients')) {
            $newMessagesQuery->with('recipients:id,name');
        }

        $newMessages = $newMessagesQuery
            ->visibleTo($currentUserId)
            ->where('id', '>', $sinceId)
            ->orderBy('id')
            ->limit(20)
            ->get()
            ->map(function (Chat $chat) use ($currentUserId) {
                $preview = $chat->message !== ''
                    ? Str::limit($chat->message, 80)
                    : match ($chat->type) {
                        'image' => 'Mengirim foto',
                        'video' => 'Mengirim video',
                        'audio', 'voice' => 'Mengirim audio',
                        'file' => 'Mengirim file',
                        default => 'Pesan baru',
                    };

                if ($chat->type === 'file') {
                    $fileName = $chat->file_path ? basename($chat->file_path) : 'file';
                    $preview = $fileName;
                }

                if ($chat->type === 'voice') {
                    $preview = 'Mengirim voice note';
                }

                return [
                    'id' => $chat->id,
                    'sender' => $chat->user?->name ?? 'Unknown',
                    'preview' => $preview,
                    'type' => $chat->type,
                    'is_mine' => (int) $chat->user_id === $currentUserId,
                    'created_at' => $chat->created_at?->toIso8601String(),
                ];
            })
            ->values();

        return response()->json([
            'signature' => $signature,
            'latest_id' => $latestId,
            'count' => $totalCount,
            'new_messages' => $newMessages,
        ]);
    }
}
