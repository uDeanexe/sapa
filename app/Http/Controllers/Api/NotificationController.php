<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Mengambil semua notifikasi user.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->get()->map(function($n) {
            return [
                'id'         => $n->id,
                'title'      => $n->data['title'] ?? 'No Title',
                'message'    => $n->data['message'] ?? '',
                'type'       => $n->data['type'] ?? 'general',
                'category'   => $n->data['category'] ?? 'general',
                'label'      => $n->data['label'] ?? 'Info',
                'icon'       => $n->data['icon'] ?? 'bell',
                'color'      => $n->data['color'] ?? 'slate',
                'route'      => $n->data['route'] ?? null,
                'route_id'   => $n->data['route_id'] ?? null,
                'is_read'    => $n->read_at !== null, // Hapus tulisan syntax error di sini
                'created_at' => $n->created_at?->toIso8601String(),
                'created_at_human' => $n->created_at?->diffForHumans(),
            ];
        });

        return response()->json($notifications);
    }

    /**
     * Menandai semua notifikasi yang belum dibaca menjadi sudah dibaca.
     */
    public function markRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        Cache::forget('notif_unread_count_'.$request->user()->id);
        return response()->json(['message' => 'Semua ditandai dibaca']);
    }

    /**
     * Mengambil jumlah notifikasi yang belum dibaca dan data terbaru.
     */
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();
        $cacheKey = 'notif_unread_count_'.$user->id;

        $payload = Cache::remember($cacheKey, now()->addSeconds(5), function () use ($user) {
            $latest = $user->unreadNotifications()->latest()->first();

            return [
                'unread_count' => $user->unreadNotifications()->count(),
                'latest_title' => $latest ? ($latest->data['title'] ?? null) : null,
                'latest_message' => $latest ? ($latest->data['message'] ?? null) : null,
                'latest_type' => $latest ? ($latest->data['type'] ?? 'general') : null,
                'latest_category' => $latest ? ($latest->data['category'] ?? 'general') : null,
                'latest_label' => $latest ? ($latest->data['label'] ?? 'Info') : null,
            ];
        });

        return response()->json($payload);
    }
}
