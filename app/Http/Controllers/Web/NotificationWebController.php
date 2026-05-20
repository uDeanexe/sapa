<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationWebController extends Controller
{
    public function poll(Request $request): JsonResponse
    {
        $request->validate([
            'since_id' => ['nullable', 'string', 'max:80'],
        ]);

        $user = $request->user();
        $sinceId = (string) $request->query('since_id', '');
        $sinceNotification = $sinceId !== ''
            ? $user->notifications()->where('id', $sinceId)->first()
            : null;

        $query = $user->notifications()
            ->when($sinceNotification, fn ($q) => $q->where('created_at', '>', $sinceNotification->created_at))
            ->latest()
            ->limit(10);

        $notifications = $query->get()
            ->sortBy('created_at')
            ->values()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notifikasi',
                'message' => $notification->data['message'] ?? '',
                'type' => $notification->data['type'] ?? 'general',
                'category' => $notification->data['category'] ?? 'general',
                'label' => $notification->data['label'] ?? 'Info',
                'icon' => $notification->data['icon'] ?? 'bell',
                'color' => $notification->data['color'] ?? 'slate',
                'route' => $notification->data['route'] ?? null,
                'route_id' => $notification->data['route_id'] ?? null,
                'is_read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'latest_id' => (string) ($user->notifications()->latest()->value('id') ?? ''),
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }
}
