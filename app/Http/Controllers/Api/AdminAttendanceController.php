<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Presence;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless($user, 401);
        $role = (string) ($user->role ?? '');
        abort_unless(in_array($role, ['admin', 'kepala'], true), 403);
    }

    public function presences(Request $request)
    {
        $this->ensureAdmin($request);

        $presences = Presence::with('user:id,name')
            ->where(function ($q) {
                $q->where('is_approved', 'pending')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('check_out')
                            ->where(function ($q3) {
                                $q3->where('is_approved_out', 'pending')
                                    ->orWhereNull('is_approved_out');
                            });
                    });
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $presences,
        ]);
    }

    public function leaves(Request $request)
    {
        $this->ensureAdmin($request);

        $status = $request->query('status');
        $leaves = Leave::with('user:id,name')
            ->when(is_string($status) && in_array($status, ['pending', 'approved', 'rejected'], true), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $leaves,
        ]);
    }

    public function updateLeave(Request $request, int $id)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'status' => "required|in:approved,rejected",
        ]);

        $leave = Leave::findOrFail($id);
        $leave->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'data' => $leave->load('user:id,name'),
        ]);
    }

    public function updatePresence(Request $request, int $id)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'status' => "required|in:approved,rejected",
            'type' => "nullable|in:in,out",
        ]);

        $presence = Presence::with('user:id,name')->findOrFail($id);
        $type = (string) ($validated['type'] ?? 'in');

        if ($type === 'out') {
            $presence->is_approved_out = $validated['status'];
        } else {
            $presence->is_approved = $validated['status'];
        }

        $presence->save();

        return response()->json([
            'success' => true,
            'data' => $presence,
        ]);
    }
}
