<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Leave;
use App\Models\Presence;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $role = (string) ($user->role ?? '');
        abort_unless(in_array($role, ['admin', 'kepala'], true), 403);

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $todayJobs = Job::with(['cs:id,name', 'technician:id,name,division_id', 'technician.division:id,name'])
            ->whereDate('created_at', $today)
            ->latest()
            ->limit(30)
            ->get();

        $yesterdayJobs = Job::with(['cs:id,name', 'technician:id,name,division_id', 'technician.division:id,name'])
            ->whereDate('created_at', $yesterday)
            ->latest()
            ->limit(30)
            ->get();

        $activeJobs = Job::with(['cs:id,name', 'technician:id,name,division_id', 'technician.division:id,name'])
            ->whereIn('status', ['pending', 'process'])
            ->latest()
            ->limit(8)
            ->get();

        $todayPresences = Presence::with('user:id,name')
            ->whereDate('date', $today)
            ->get();

        $pendingPresenceApprovals = Presence::where(function ($query) {
            $query->where('is_approved', 'pending')
                ->orWhere(function ($subQuery) {
                    $subQuery->whereNotNull('check_out')
                        ->where(function ($approvalQuery) {
                            $approvalQuery->where('is_approved_out', 'pending')
                                ->orWhereNull('is_approved_out');
                        });
                });
        })->count();

        $pendingLeaves = Leave::where('status', 'pending')->count();
        $employeesCount = User::where('role', 'karyawan')->count();

        // Lightweight job status counts.
        $jobStatusCounts = Job::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [
            'today_jobs' => $todayJobs->count(),
            'yesterday_jobs' => $yesterdayJobs->count(),
            'active_jobs' => $activeJobs->count(),
            'completed_jobs' => (int) ($jobStatusCounts['completed'] ?? 0),
            'pending_jobs' => (int) ($jobStatusCounts['pending'] ?? 0),
            'process_jobs' => (int) ($jobStatusCounts['process'] ?? 0),
            'today_attendance' => $todayPresences->count(),
            'pending_presence_approvals' => $pendingPresenceApprovals,
            'pending_leaves' => $pendingLeaves,
            'employees' => $employeesCount,
        ];

        return response()->json([
            'summary' => $summary,
            'today_jobs' => $todayJobs,
            'yesterday_jobs' => $yesterdayJobs,
            'active_jobs' => $activeJobs,
            'today_presences' => $todayPresences,
        ]);
    }
}

