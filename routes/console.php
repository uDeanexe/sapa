<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Chat;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chat:delete {id : Chat ID} {--dry-run : Tampilkan aksi tanpa menghapus} {--force : Lewati konfirmasi} {--keep-files : Jangan hapus file media}', function () {
    $id = (int) $this->argument('id');
    $dryRun = (bool) $this->option('dry-run');
    $force = (bool) $this->option('force');
    $keepFiles = (bool) $this->option('keep-files');

    $chat = Chat::query()->find($id);
    if (! $chat) {
        $this->error("Chat id=$id tidak ditemukan.");
        return 1;
    }

    $this->line("Target: chat id={$chat->id} type={$chat->type} user_id={$chat->user_id}");
    $this->line('Message: '.Str::limit((string) ($chat->message ?? ''), 120));
    $this->line('File path: '.($chat->file_path ?? '(none)'));

    if (! $force && ! $dryRun) {
        if (! $this->confirm("Yakin hapus chat id={$chat->id} ? (sekalian file media)")) {
            $this->warn('Dibatalkan.');
            return 0;
        }
    }

    $deleted = [];
    $skipped = [];

    $normalizePath = function (?string $filePath): ?string {
        if (! $filePath) return null;
        $raw = ltrim($filePath, '/');
        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $raw = ltrim(parse_url($raw, PHP_URL_PATH) ?: '', '/');
        }
        if (Str::startsWith($raw, 'storage/')) {
            $raw = Str::after($raw, 'storage/');
        }
        if (Str::startsWith($raw, 'public/')) {
            $raw = Str::after($raw, 'public/');
        }
        return $raw !== '' ? $raw : null;
    };

    $maybeDeleteFile = function (string $path) use (&$deleted, &$skipped, $dryRun) {
        if ($path === '') return;

        // Try storage disks
        foreach (['local', 'public'] as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    if ($dryRun) {
                        $skipped[] = "$disk:$path (dry-run)";
                    } else {
                        Storage::disk($disk)->delete($path);
                        $deleted[] = "$disk:$path";
                    }
                }
            } catch (\Throwable $e) {
                $skipped[] = "$disk:$path (error: ".$e->getMessage().')';
            }
        }

        // Legacy public paths (deployment lama)
        $candidates = [
            public_path($path),
            public_path('uploads/'.basename($path)),
        ];
        foreach ($candidates as $fsPath) {
            if (is_string($fsPath) && is_file($fsPath)) {
                if ($dryRun) {
                    $skipped[] = "fs:$fsPath (dry-run)";
                } else {
                    @unlink($fsPath);
                    $deleted[] = "fs:$fsPath";
                }
            }
        }
    };

    DB::transaction(function () use ($chat, $keepFiles, $normalizePath, $maybeDeleteFile) {
        // Jika ada FK/constraint parent_id, amanin dulu.
        Chat::query()->where('parent_id', $chat->id)->update(['parent_id' => null]);

        if (! $keepFiles) {
            $path = $normalizePath($chat->file_path);
            if ($path) {
                $maybeDeleteFile($path);
            }
        }

        $chat->delete();
    });

    $this->info($dryRun ? 'Dry-run selesai.' : 'Chat berhasil dihapus.');
    if (! empty($deleted)) {
        $this->line('Deleted files:');
        foreach ($deleted as $d) {
            $this->line("- $d");
        }
    }
    if (! empty($skipped)) {
        $this->line('Notes:');
        foreach ($skipped as $s) {
            $this->line("- $s");
        }
    }

    return 0;
})->purpose('Hapus chat tertentu beserta file medianya (aman + opsi dry-run)');
