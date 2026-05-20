<?php

namespace App\Notifications;

use App\Mail\InternalNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Route;

class InternalNotification extends Notification
{
    use Queueable;

    protected array $details;

    private const TYPE_META = [
        'chat' => [
            'category' => 'chat',
            'label' => 'Chat',
            'icon' => 'comments',
            'color' => 'blue',
        ],
        'leave' => [
            'category' => 'permission',
            'label' => 'Izin',
            'icon' => 'file-signature',
            'color' => 'amber',
        ],
        'presence' => [
            'category' => 'attendance',
            'label' => 'Presensi',
            'icon' => 'calendar-check',
            'color' => 'emerald',
        ],
        'job_assigned' => [
            'category' => 'job',
            'label' => 'Tugas',
            'icon' => 'briefcase',
            'color' => 'indigo',
        ],
        'job' => [
            'category' => 'job',
            'label' => 'Tugas',
            'icon' => 'briefcase',
            'color' => 'indigo',
        ],
        'checklist' => [
            'category' => 'checklist',
            'label' => 'Checklist',
            'icon' => 'list-check',
            'color' => 'sky',
        ],
        'general' => [
            'category' => 'general',
            'label' => 'Info',
            'icon' => 'bell',
            'color' => 'slate',
        ],
    ];

    /**
     * @param array<string, mixed> $details
     */
    public function __construct(array $details)
    {
        $this->details = $details;
    }

    public function via($notifiable): array
    {
        $channels = ['database'];

        $broadcastDriver = (string) config('broadcasting.default', 'log');
        if ($broadcastDriver !== 'log' && $broadcastDriver !== 'null') {
            $channels[] = 'broadcast';
        }

        if ($this->shouldSendMail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Hindari broadcast via queue database, karena tabel `jobs` di aplikasi ini
     * digunakan untuk fitur Job (bukan queue jobs table Laravel).
     */
    public function shouldBroadcastNow(): bool
    {
        return true;
    }

    public function toMail($notifiable): InternalNotificationMail
    {
        return (new InternalNotificationMail(
            $this->safeText($this->details['title'] ?? 'Notifikasi'),
            $this->safeText($this->details['message'] ?? 'Ada notifikasi baru.'),
            $this->mailActionUrl(),
            $this->safeText($this->details['action_label'] ?? 'Buka Aplikasi')
        ))->to($notifiable->email);
    }

    public function toArray($notifiable): array
    {
        $type = (string) ($this->details['type'] ?? 'general');
        $meta = self::TYPE_META[$type] ?? self::TYPE_META['general'];
        $title = $this->safeText($this->details['title'] ?? 'Notifikasi');
        $message = $this->safeText($this->details['message'] ?? '');

        if ($notifiable->fcm_token) {
            app(\App\Services\FcmPushService::class)->sendToToken(
                $notifiable->fcm_token,
                $title,
                $message,
                [
                    'type'     => $type,
                    'category' => $meta['category'],
                    'label'    => $meta['label'],
                    'route'    => (string) ($this->details['route'] ?? ''),
                    'route_id' => (string) ($this->details['route_id'] ?? ''),
                ]
            );
        }

        return [
            'title'    => $title,
            'message'  => $message,
            'type'     => $type,
            'category' => $meta['category'],
            'label'    => $meta['label'],
            'icon'     => $meta['icon'],
            'color'    => $meta['color'],
            'route'    => $this->details['route'] ?? null,
            'route_id' => $this->details['route_id'] ?? null,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    private function shouldSendMail($notifiable): bool
    {
        if (empty($notifiable->email)) {
            return false;
        }

        return filter_var($this->details['mail'] ?? $this->details['email'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    private function mailActionUrl(): string
    {
        if (!empty($this->details['action_url']) && filter_var($this->details['action_url'], FILTER_VALIDATE_URL)) {
            return (string) $this->details['action_url'];
        }

        $route = $this->details['route'] ?? null;
        if (is_string($route) && Route::has($route)) {
            return route($route);
        }

        return url('/dashboard');
    }

    private function safeText(mixed $value): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags((string) $value))) ?: 'Notifikasi';
    }
}
