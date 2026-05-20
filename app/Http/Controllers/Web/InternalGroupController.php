<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InternalGroupController extends Controller
{
    public function status(): JsonResponse
    {
        $now = now()->timestamp;
        $onlineWindowSeconds = 5 * 60;
        $threshold = $now - $onlineWindowSeconds;

        $onlineIds = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $threshold)
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $users = User::query()
            ->select(['id', 'name', 'role'])
            ->orderBy('name')
            ->limit(800)
            ->get()
            ->map(function (User $user) use ($onlineIds) {
                $id = (int) $user->id;
                return [
                    'id' => $id,
                    'name' => (string) ($user->name ?? ''),
                    'role' => (string) ($user->role ?? ''),
                    'is_online' => in_array($id, $onlineIds, true),
                ];
            })
            ->values();

        return response()->json([
            'online_window_seconds' => $onlineWindowSeconds,
            'users' => $users,
        ]);
    }
}

