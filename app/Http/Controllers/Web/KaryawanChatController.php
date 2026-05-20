<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\ChatController as ApiChatController;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatSeen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class KaryawanChatController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $messagesQuery = Chat::query()
            ->with(['user', 'parent.user', 'seenBy']);

        if (Schema::hasTable('chat_recipients')) {
            $messagesQuery->with('recipients:id,name');
        }

        $messages = $messagesQuery
            ->visibleTo((int) $userId)
            ->orderBy('created_at', 'asc')
            ->get();
        $selectedChat = $messages->last() ?? new Chat();
        $scrollToLatest = true;

        $unseenChatIds = Chat::query()
            ->visibleTo((int) $userId)
            ->where('user_id', '!=', $userId)
            ->whereDoesntHave('seenBy', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->pluck('id');

        if ($unseenChatIds->isNotEmpty()) {
            $now = now();
            $rows = $unseenChatIds->map(fn ($id) => [
                'chat_id' => $id,
                'user_id' => $userId,
                'seen_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            ChatSeen::insertOrIgnore($rows);
        }

        return view('karyawan.Chats.index', compact('messages', 'selectedChat', 'scrollToLatest'));
    }

    public function send(Request $request, ?int $id = null)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:10240',
        ]);

        if (! $request->filled('message') && ! $request->hasFile('file')) {
            return redirect()->back()->with('error', 'Pesan atau lampiran diperlukan.');
        }

        $type = 'text';
        if ($request->hasFile('file')) {
            $mime = $request->file('file')?->getMimeType() ?? '';
            if (str_starts_with($mime, 'image/')) {
                $type = 'image';
            } elseif (str_starts_with($mime, 'video/')) {
                $type = 'video';
            } elseif (str_starts_with($mime, 'audio/')) {
                $type = 'audio';
            } else {
                $type = 'file';
            }
        }

        $request->merge(['type' => $type]);

        return app(ApiChatController::class)->store($request);
    }

    public function messages(?int $id = null)
    {
        $userId = (int) Auth::id();
        $messagesQuery = Chat::query()
            ->with(['user', 'parent.user', 'seenBy']);

        if (Schema::hasTable('chat_recipients')) {
            $messagesQuery->with('recipients:id,name');
        }

        $messages = $messagesQuery
            ->visibleTo($userId)
            ->orderBy('created_at', 'asc')
            ->get();
        $scrollToLatest = true;

        return view('components.messages-list', compact('messages', 'scrollToLatest'));
    }
}
