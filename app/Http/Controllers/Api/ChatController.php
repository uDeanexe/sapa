<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\ChatCreated;
use App\Models\Chat;
use App\Models\ChatSeen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class ChatController extends Controller
{
    public function index()
    {
        $currentUserId = (int) Auth::id();
        $query = Chat::with(['user', 'parent.user'])
            ->withCount('seenBy')
            ->visibleTo($currentUserId)
            ->orderBy('created_at', 'asc');

        if (Schema::hasTable('chat_recipients')) {
            $query->withCount('recipients');
        }

        $chats = $query->get()
            ->map(fn ($chat) => $this->formatChat($chat));

        return response()->json($chats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:text,image,video,audio,voice,file',
            'message' => 'required_if:type,text|nullable|string|max:5000',
            'file' => 'required_unless:type,text|file',
            'parent_id' => 'nullable|integer',
        ]);

        $filePath = null;
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $filePath = $request->file('file')->store('chat-uploads', 'local');
            $filePath = ltrim($filePath, '/');

            if ($request->type === 'video') {
                $filePath = $this->compressVideoIfPossible($filePath);
                $filePath = $this->encryptVideoForStorage($filePath);
            }
        }

        $chat = $this->createChat($request, $filePath);

        if ($request->wantsJson() || $request->is('api/*')) {
            $chat->load(['user', 'parent.user']);

            return response()->json($this->formatChat($chat), 201);
        }

        return back();
    }

    public function uploadChunk(Request $request)
    {
        $validated = $request->validate([
            'upload_id' => 'required|string|max:80|regex:/^[A-Za-z0-9_-]+$/',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1|max:10000',
            'chunk' => 'required|file',
        ]);

        if ($validated['chunk_index'] >= $validated['total_chunks']) {
            throw ValidationException::withMessages([
                'chunk_index' => 'Index chunk tidak valid.',
            ]);
        }

        $directory = 'chat-chunks/'.$validated['upload_id'];
        Storage::disk('local')->putFileAs(
            $directory,
            $request->file('chunk'),
            'part-'.$validated['chunk_index']
        );

        return response()->json([
            'success' => true,
            'upload_id' => $validated['upload_id'],
            'received_chunk' => $validated['chunk_index'],
        ]);
    }

    public function completeChunkUpload(Request $request)
    {
        $validated = $request->validate([
            'upload_id' => 'required|string|max:80|regex:/^[A-Za-z0-9_-]+$/',
            'total_chunks' => 'required|integer|min:1|max:10000',
            'type' => 'required|in:video',
            'message' => 'nullable|string|max:5000',
            'parent_id' => 'nullable|integer',
            'file_name' => 'required|string|max:255',
        ]);

        $filePath = $this->assembleChunkedUpload(
            $validated['upload_id'],
            $validated['total_chunks'],
            $validated['file_name']
        );

        $filePath = $this->compressVideoIfPossible($filePath);
        $filePath = $this->encryptVideoForStorage($filePath);

        $chat = $this->createChat($request, $filePath);

        return response()->json($this->formatChat($chat->load(['user', 'parent.user'])), 201);
    }

    private function createChat(Request $request, ?string $filePath): Chat
    {
        $sender = Auth::user();
        $rawMessage = (string) $request->input('message', '');
        $mentionedUserIds = $this->extractMentionedUserIds($rawMessage);
        $parentId = $request->input('parent_id');

        $parentChat = null;
        if ($parentId !== null && $parentId !== '') {
            $parentId = (int) $parentId;
            $parentQuery = Chat::query()->with('recipients:id')->visibleTo((int) $sender->id);
            $parentChat = $parentQuery->find($parentId);

            if (! $parentChat) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Pesan yang dibalas tidak ditemukan atau tidak dapat diakses.',
                ]);
            }
        }

        if (! empty($mentionedUserIds) && ! Schema::hasTable('chat_recipients')) {
            throw ValidationException::withMessages([
                'message' => 'Fitur tag private belum aktif (tabel chat_recipients belum ada). Jalankan migrate terlebih dahulu.',
            ]);
        }

        $chat = Chat::create([
            'user_id' => $sender->id,
            'message' => $rawMessage,
            'type' => $request->type,
            'file_path' => $filePath,
            'parent_id' => $parentChat?->id,
            'is_pinned' => false,
            'is_edited' => false,
        ]);

        if (Schema::hasTable('chat_recipients')) {
            if (! empty($mentionedUserIds)) {
                $chat->recipients()->sync(array_values(array_unique(array_map('intval', $mentionedUserIds))));
            } elseif ($parentChat && $parentChat->relationLoaded('recipients') && $parentChat->recipients->isNotEmpty()) {
                $chat->recipients()->sync(
                    $parentChat->recipients->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
                );
            }
        }

        $this->notifyOtherUsers($chat, $sender, $mentionedUserIds);
        try {
            broadcast(new ChatCreated($chat))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        return $chat;
    }

    private function notifyOtherUsers(Chat $chat, User $sender, array $mentionedUserIds = []): void
    {
        $otherUsersQuery = User::query()->where('id', '!=', $sender->id);

        if (! empty($mentionedUserIds)) {
            // Request: when tagging, notify only one intended recipient.
            $targetIds = array_values(array_unique(array_map('intval', $mentionedUserIds)));
            $targetIds = array_slice($targetIds, 0, 1);
            $otherUsersQuery->whereIn('id', $targetIds);
        }

        $otherUsers = $otherUsersQuery->get();
        $messagePreview = '';
        if ($chat->message && $chat->message !== '') {
            $messagePreview = Str::limit($this->stripMentionMarkup($chat->message), 60);
        } elseif ($chat->type === 'image') {
            $messagePreview = '📷 Mengirim foto';
        } elseif ($chat->type === 'video') {
            $messagePreview = '🎥 Mengirim video';
        } elseif ($chat->type === 'audio' || $chat->type === 'voice') {
            $messagePreview = '🎵 Mengirim audio';
        } elseif ($chat->type === 'file') {
            $messagePreview = '📎 Mengirim file';
        }

        foreach ($otherUsers as $user) {
            $user->notify(new \App\Notifications\InternalNotification([
                'title' => empty($mentionedUserIds) ? $sender->name : 'Anda ditag oleh '.$sender->name,
                'message' => $messagePreview,
                'type' => empty($mentionedUserIds) ? 'chat' : 'chat_mention',
                'route' => 'chat',
            ]));
        }
    }

    private function extractMentionedUserIds(string $message): array
    {
        if ($message === '') {
            return [];
        }

        // Primary format: "@[id|Name]"
        preg_match_all('/@\\[(\\d+)\\|[^\\]]+\\]/', $message, $matches);
        $ids = collect($matches[1] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique();

        // Fallback format: "@handle" (mobile typing) -> resolve to user id.
        // This prevents accidental "notify everyone" when the client hasn't normalized yet.
        preg_match_all(
            "/(^|[\\s\\(\\[\\{\\\"'\\.,;:!?])@([A-Za-z0-9_]{2,40})\\b/",
            $message,
            $handleMatches
        );
        $handles = collect($handleMatches[2] ?? [])
            ->map(fn ($h) => strtolower(trim((string) $h)))
            ->filter(fn ($h) => $h !== '')
            ->unique();

        if ($handles->isNotEmpty()) {
            // Limit: resolve at most 1 user for notification scope (as requested).
            $handle = (string) $handles->first();
            $user = User::query()
                ->select(['id', 'name'])
                ->get()
                ->first(function ($u) use ($handle) {
                    return $this->buildHandleFromName((string) ($u->name ?? '')) === $handle;
                });

            if ($user && (int) $user->id > 0) {
                $ids->push((int) $user->id);
            }
        }

        $ids = $ids->filter(fn ($id) => (int) $id > 0)->unique()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $ids->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function buildHandleFromName(string $name): string
    {
        $raw = strtolower(trim($name));
        $compact = preg_replace('/\\s+/', '', $raw) ?? '';
        $safe = preg_replace('/[^a-z0-9_]/', '', $compact) ?? '';
        if (strlen($safe) < 2) {
            return '';
        }

        return strlen($safe) > 40 ? substr($safe, 0, 40) : $safe;
    }

    private function stripMentionMarkup(string $message): string
    {
        return preg_replace('/@\\[(\\d+)\\|([^\\]]+)\\]/', '@$2', $message) ?? $message;
    }

    public function update(Request $request, $id)
    {
        $chat = Chat::findOrFail($id);

        if ((int) $chat->user_id !== (int) Auth::id()) {
            return request()->wantsJson() ? response()->json(['message' => 'Tidak diizinkan'], 403) : back();
        }

        $request->validate(['message' => 'required|string|max:5000']);

        $chat->update([
            'message' => $request->input('message'),
            'is_edited' => true,
        ]);
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Pesan diperbarui', 'chat' => $chat]);
        }

        return back()->with('success', 'Pesan berhasil diedit');
    }

    public function destroy($id)
    {
        $chat = Chat::findOrFail($id);
        if ($chat->user_id !== Auth::id()) {
            return request()->wantsJson() ? response()->json(['message' => 'Tidak diizinkan'], 403) : back();
        }

        if ($chat->isEncryptedVideo() && Storage::disk('local')->exists($chat->file_path)) {
            Storage::disk('local')->delete($chat->file_path);
        } elseif ($chat->file_path && Storage::disk('public')->exists($chat->file_path)) {
            Storage::disk('public')->delete($chat->file_path);
        }

        ChatSeen::where('chat_id', $id)->delete();
        $chat->delete();

        return (request()->wantsJson() || request()->is('api/*'))
            ? response()->json(['message' => 'Pesan dihapus'])
            : back();
    }

    public function pin($id)
    {
        Chat::findOrFail($id)->update(['is_pinned' => true]);

        return (request()->wantsJson() || request()->is('api/*'))
            ? response()->json(['message' => 'Pesan dipin', 'is_pinned' => true])
            : back();
    }

    public function unpin($id)
    {
        Chat::findOrFail($id)->update(['is_pinned' => false]);

        return (request()->wantsJson() || request()->is('api/*'))
            ? response()->json(['message' => 'Pin dihapus', 'is_pinned' => false])
            : back();
    }

    public function markSeen($id)
    {
        $userId = Auth::id();
        ChatSeen::firstOrCreate(
            ['chat_id' => $id, 'user_id' => $userId],
            ['seen_at' => now()]
        );

        return response()->json(['message' => 'Ditandai dilihat']);
    }

    public function seenBy($id)
    {
        Chat::findOrFail($id);
        $seenList = ChatSeen::where('chat_id', $id)
            ->with('user:id,name')
            ->orderBy('seen_at', 'asc')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->user->id ?? null,
                'name' => $s->user->name ?? 'Unknown',
                'seen_at' => $s->seen_at ? $s->seen_at->toIso8601String() : null,
            ]);

        return response()->json($seenList);
    }

    private function formatChat(Chat $chat): array
    {
        $recipientsCount = 0;
        if (Schema::hasTable('chat_recipients')) {
            $recipientsCount = (int) ($chat->recipients_count ?? 0);
        }

        return [
            'id' => $chat->id,
            'user_id' => $chat->user_id,
            'user' => $chat->user ? ['id' => $chat->user->id, 'name' => $chat->user->name] : null,
            'message' => $chat->message,
            'type' => $chat->type,
            'file_path' => $chat->public_file_path,
            'file_url' => $this->publicFileUrl($chat),
            'parent_id' => $chat->parent_id,
            'parent' => $chat->parent ? [
                'id' => $chat->parent->id,
                'message' => $chat->parent->message,
                'type' => $chat->parent->type,
                'file_path' => $chat->parent->public_file_path,
                'file_url' => $this->publicFileUrl($chat->parent),
                'user' => $chat->parent->user ? ['name' => $chat->parent->user->name] : null,
            ] : null,
            'is_pinned' => (bool) $chat->is_pinned,
            'is_edited' => (bool) $chat->is_edited,
            'is_private' => $recipientsCount > 0,
            'recipients_count' => $recipientsCount,
            'seen_by_count' => $chat->seen_by_count ?? $chat->seenBy()->count(),
            'created_at' => $chat->created_at?->toIso8601String(),
            'updated_at' => $chat->updated_at?->toIso8601String(),
        ];
    }

    private function publicFileUrl(Chat $chat): ?string
    {
        if (! $chat->file_path) {
            return null;
        }

        return route('api.chats.media', $chat);
    }

    private function encryptVideoForStorage(string $path): string
    {
        $source = Storage::disk('local')->path($path);
        $encryptedPath = 'encrypted-videos/'.pathinfo($path, PATHINFO_FILENAME).'.'.pathinfo($path, PATHINFO_EXTENSION).'.enc';

        Storage::disk('local')->put($encryptedPath, Crypt::encryptString(file_get_contents($source)));
        Storage::disk('local')->delete($path);

        return $encryptedPath;
    }

    private function assembleChunkedUpload(string $uploadId, int $totalChunks, string $fileName): string
    {
        $chunkDirectory = 'chat-chunks/'.$uploadId;

        for ($i = 0; $i < $totalChunks; $i++) {
            if (! Storage::disk('local')->exists($chunkDirectory.'/part-'.$i)) {
                throw ValidationException::withMessages([
                    'upload_id' => 'Chunk upload belum lengkap.',
                ]);
            }
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) ?: 'mp4';
        $safeName = Str::uuid().'.'.$extension;
        $assembledPath = 'chat-uploads/'.$safeName;
        $target = Storage::disk('local')->path($assembledPath);

        if (! is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }

        $output = fopen($target, 'wb');

        try {
            for ($i = 0; $i < $totalChunks; $i++) {
                $partPath = Storage::disk('local')->path($chunkDirectory.'/part-'.$i);
                $input = fopen($partPath, 'rb');
                stream_copy_to_stream($input, $output);
                fclose($input);
            }
        } finally {
            fclose($output);
            Storage::disk('local')->deleteDirectory($chunkDirectory);
        }

        return $assembledPath;
    }

    private function compressVideoIfPossible(string $path): string
    {
        $ffmpeg = env('CHAT_FFMPEG_PATH', 'ffmpeg');
        $source = Storage::disk('local')->path($path);
        $compressedPath = 'chat-uploads/'.pathinfo($path, PATHINFO_FILENAME).'-compressed.mp4';
        $target = Storage::disk('local')->path($compressedPath);

        try {
            $check = new Process([$ffmpeg, '-version']);
            $check->setTimeout(5);
            $check->run();

            if (! $check->isSuccessful()) {
                return $path;
            }

            $process = new Process([
                $ffmpeg,
                '-y',
                '-i',
                $source,
                '-vf',
                "scale=w='min(1280,iw)':h=-2",
                '-c:v',
                'libx264',
                '-preset',
                'veryfast',
                '-crf',
                '28',
                '-c:a',
                'aac',
                '-b:a',
                '96k',
                '-movflags',
                '+faststart',
                $target,
            ]);
            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful() || ! file_exists($target) || filesize($target) >= filesize($source)) {
                if (file_exists($target)) {
                    unlink($target);
                }

                return $path;
            }

            Storage::disk('local')->delete($path);

            return $compressedPath;
        } catch (\Throwable $e) {
            if (file_exists($target)) {
                unlink($target);
            }

            return $path;
        }
    }
}
