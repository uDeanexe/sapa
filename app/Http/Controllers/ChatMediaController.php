<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ChatMediaController extends Controller
{
    public function show(Chat $chat): Response
    {
        abort_unless($chat->file_path, 404);

        $raw = ltrim($chat->file_path, '/');
        // Normalisasi path legacy: kadang tersimpan sebagai URL atau `storage/...`.
        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $raw = ltrim(parse_url($raw, PHP_URL_PATH) ?: '', '/');
        }
        if (Str::startsWith($raw, 'storage/')) {
            $raw = Str::after($raw, 'storage/');
        }
        if (Str::startsWith($raw, 'public/')) {
            $raw = Str::after($raw, 'public/');
        }

        if ($chat->isEncryptedVideo()) {
            abort_unless(Storage::disk('local')->exists($chat->file_path), 404);

            // NOTE: HTML5 video playback (seeking/metadata) generally requires Range requests.
            // BinaryFileResponse supports Range, but streaming from decrypted string does not.
            // So we decrypt once to a temporary local file and serve that file.
            $tmpPath = $this->ensureDecryptedVideoTempFile($chat);

            return response()->file($tmpPath, [
                'Content-Type' => $this->videoMimeType($chat->file_path),
                'Content-Disposition' => 'inline; filename="'.$this->downloadName($chat).'"',
                'Cache-Control' => 'private, max-age=0, no-cache',
            ]);
        }

        // Private storage (current)
        if (Storage::disk('local')->exists($raw)) {
            return response()->file(Storage::disk('local')->path($raw), [
                'Content-Disposition' => 'inline; filename="'.$this->downloadName($chat).'"',
                'Cache-Control' => 'private, max-age=0, no-cache',
            ]);
        }

        // Backward-compat: Laravel public disk (storage/app/public)
        if (Storage::disk('public')->exists($raw)) {
            return response()->file(Storage::disk('public')->path($raw), [
                'Content-Disposition' => 'inline; filename="'.$this->downloadName($chat).'"',
                'Cache-Control' => 'private, max-age=0, no-cache',
            ]);
        }

        // Backward-compat: beberapa deployment lama menyimpan di `public/uploads/*`
        // (bukan `storage/app/public/uploads/*`).
        $candidates = [
            public_path($raw),
            public_path('uploads/'.basename($raw)),
        ];
        foreach ($candidates as $path) {
            if (is_string($path) && is_file($path)) {
                return response()->file($path, [
                    'Content-Disposition' => 'inline; filename="'.$this->downloadName($chat).'"',
                    'Cache-Control' => 'private, max-age=0, no-cache',
                ]);
            }
        }

        abort(404);
    }

    private function ensureDecryptedVideoTempFile(Chat $chat): string
    {
        $encryptedPath = $chat->file_path;
        $encryptedAbsolute = Storage::disk('local')->path($encryptedPath);
        $encryptedMtime = @filemtime($encryptedAbsolute) ?: 0;

        $extension = strtolower(pathinfo(Str::beforeLast($encryptedPath, '.enc'), PATHINFO_EXTENSION)) ?: 'mp4';
        $cacheKey = md5($encryptedPath.'|'.$encryptedMtime);
        $relative = 'tmp/chat-media/'.$chat->id.'-'.$cacheKey.'.'.$extension;

        if (Storage::disk('local')->exists($relative)) {
            return Storage::disk('local')->path($relative);
        }

        $encrypted = Storage::disk('local')->get($encryptedPath);
        $bytes = Crypt::decryptString($encrypted);
        Storage::disk('local')->put($relative, $bytes);

        return Storage::disk('local')->path($relative);
    }

    private function videoMimeType(string $path): string
    {
        $extension = strtolower(pathinfo(Str::beforeLast($path, '.enc'), PATHINFO_EXTENSION));

        return match ($extension) {
            'mp4', 'm4v' => 'video/mp4',
            'mov' => 'video/quicktime',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            'avi' => 'video/x-msvideo',
            '3gp' => 'video/3gpp',
            default => 'application/octet-stream',
        };
    }

    private function downloadName(Chat $chat): string
    {
        $name = basename(Str::beforeLast($chat->file_path, '.enc'));

        return str_replace('"', '', $name ?: 'video.mp4');
    }
}
