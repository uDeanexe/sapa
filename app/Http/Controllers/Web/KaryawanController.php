<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\PresenceController as ApiPresenceController;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Holiday;
use App\Models\Job;
use App\Models\Leave;
use App\Models\Presence;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KaryawanController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $today = now();

        $todayPresence = Presence::where('user_id', $user->id)
            ->whereDate('date', $today->toDateString())
            ->checkInRecords()
            ->latest('id')
            ->first();

        $todayTasks = Job::where('technician_id', $user->id)
            ->where(function ($query) use ($today) {
                $query->whereDate('start_time', $today->toDateString())
                    ->orWhereDate('end_time', $today->toDateString());
            })
            ->count();

        $newMessages = Chat::query()
            ->visibleTo((int) $user->id)
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('seenBy', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        $notificationsCount = $user->unreadNotifications()->count();

        $attendanceStats = [
            'present' => Presence::where('user_id', $user->id)
                ->whereMonth('date', $today->month)
                ->whereYear('date', $today->year)
                ->whereNotNull('check_in')
                ->count(),
            'permit' => Leave::where('user_id', $user->id)
                ->whereMonth('start_date', $today->month)
                ->whereYear('start_date', $today->year)
                ->count(),
            'absent' => 0,
            'late' => Presence::where('user_id', $user->id)
                ->whereMonth('date', $today->month)
                ->whereYear('date', $today->year)
                ->whereNotNull('check_in')
                ->where('check_in', '>', '08:00:00')
                ->count(),
        ];

        $news = $user->notifications()->latest()->limit(4)->get()->map(function ($notification) {
            return (object) [
                'id' => $notification->id,
                'title' => data_get($notification->data, 'title', 'Notifikasi'),
                'created_at' => $notification->created_at,
                'data' => $notification->data,
            ];
        });

        $activities = $user->notifications()->latest()->limit(4)->get()->map(function ($notification) {
            return (object) [
                'description' => data_get($notification->data, 'message', 'Notifikasi baru'),
                'created_at' => $notification->created_at,
                'icon' => data_get($notification->data, 'icon', 'bell'),
            ];
        });

        return view('karyawan.dashboard', compact(
            'todayPresence',
            'todayTasks',
            'newMessages',
            'notificationsCount',
            'attendanceStats',
            'news',
            'activities'
        ));
    }

    public function attendanceIndex()
    {
        $user = Auth::user();
        $today = now();

        $attendances = Presence::where('user_id', $user->id)
            ->whereMonth('date', $today->month)
            ->whereYear('date', $today->year)
            ->orderBy('date', 'desc')
            ->paginate(10);

        $stats = [
            'present' => $attendances->whereNotNull('check_in')->count(),
            'permit' => Leave::where('user_id', $user->id)
                ->whereMonth('start_date', $today->month)
                ->whereYear('start_date', $today->year)
                ->count(),
            'absent' => 0,
            'late' => $attendances->whereNotNull('check_in')->where('check_in', '>', '08:00:00')->count(),
        ];

        return view('karyawan.absen.index', compact('attendances', 'stats'));
    }

    public function attendanceCheckIn()
    {
        $user = Auth::user();

        $todayAttendance = Presence::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->checkInRecords()
            ->latest('id')
            ->first();

        return view('karyawan.absen.checkin', compact('todayAttendance'));
    }

    public function attendanceStoreCheckIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|image|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $request->merge(['notes' => $request->input('notes', 'Absen Masuk Web')]);

        $apiResponse = app(ApiPresenceController::class)->storeCheckIn($request);
        $data = $apiResponse->getData(true);

        if (! ($apiResponse->status() >= 200 && $apiResponse->status() < 300)) {
            return redirect()->back()->withInput()->with('error', $data['message'] ?? 'Gagal absen masuk.');
        }

        return redirect()->route('karyawan.attendance.checkin')->with('success', $data['message'] ?? 'Check-in berhasil.');
    }

    public function attendanceStoreCheckOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|image|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $request->merge(['notes' => $request->input('notes', 'Absen Pulang Web')]);

        $apiResponse = app(ApiPresenceController::class)->storeCheckOut($request);
        $data = $apiResponse->getData(true);

        if (! ($apiResponse->status() >= 200 && $apiResponse->status() < 300)) {
            return redirect()->back()->withInput()->with('error', $data['message'] ?? 'Gagal absen pulang.');
        }

        return redirect()->route('karyawan.attendance.checkin')->with('success', $data['message'] ?? 'Check-out berhasil.');
    }

    public function attendanceRequestPermit()
    {
        $user = Auth::user();
        $currentYear = now()->year;

        $usedLeave = Leave::where('user_id', $user->id)
            ->where('type', 'cuti')
            ->whereYear('start_date', $currentYear)
            ->count();

        $recentRequests = Leave::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $leaveQuota = [
            'annual' => 12,
            'used' => $usedLeave,
            'remaining' => max(0, 12 - $usedLeave),
        ];

        return view('karyawan.absen.request-permit', compact('leaveQuota', 'recentRequests'));
    }

    public function attendanceSubmitPermit(Request $request)
    {
        $request->validate([
            'type' => 'required|in:izin,cuti,sakit',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:2000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $request->files->set('attachment_file', $request->file('attachment'));
        }

        $request->merge(['category' => $request->input('type')]);

        $apiResponse = app(ApiPresenceController::class)->storePermission($request);
        $data = $apiResponse->getData(true);

        if (! ($apiResponse->status() >= 200 && $apiResponse->status() < 300)) {
            return redirect()->back()->withInput()->with('error', $data['message'] ?? 'Gagal mengajukan izin.');
        }

        return redirect()->route('karyawan.attendance.index')->with('success', $data['message'] ?? 'Permohonan berhasil dikirim.');
    }

    public function notifications()
    {
        // Notifikasi karyawan mengikuti pola "notifikasi biasa" (toast/global)
        // seperti admin, jadi halaman list/detail tidak dipakai lagi.
        return redirect()->route('karyawan.dashboard');
    }

    public function notificationShow($id)
    {
        return redirect()->route('karyawan.dashboard');
    }

    public function agendaIndex()
    {
        $user = Auth::user();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $holidays = Holiday::whereBetween('holiday_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('holiday_date')
            ->get()
            ->map(function ($holiday) {
                return (object) [
                    'type' => 'meeting',
                    'title' => $holiday->name ?? 'Libur Kantor',
                    'description' => 'Tanggal ini ditandai sebagai hari libur kantor.',
                    'start_date' => Carbon::parse($holiday->holiday_date),
                    'end_date' => Carbon::parse($holiday->holiday_date),
                    'start_time' => null,
                    'end_time' => null,
                    'location' => 'Kalender kantor',
                    'user_has_rsvp' => false,
                ];
            });

        $leaves = Leave::where('user_id', $user->id)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->orWhereBetween('end_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            })
            ->orderBy('start_date')
            ->get()
            ->map(function ($leave) {
                return (object) [
                    'type' => 'training',
                    'title' => 'Izin ' . ucfirst($leave->type),
                    'description' => $leave->reason ?? 'Permohonan izin dari karyawan.',
                    'start_date' => Carbon::parse($leave->start_date),
                    'end_date' => Carbon::parse($leave->end_date ?? $leave->start_date),
                    'start_time' => null,
                    'end_time' => null,
                    'location' => 'Absensi',
                    'user_has_rsvp' => true,
                ];
            });

        $events = $holidays->toBase()->concat(collect($leaves)->toBase())->values();

        return view('karyawan.agenda.index', compact('events'));
    }

    public function profile()
    {
        $user = Auth::user();

        $stats = [
            'completed_tasks' => Job::where('technician_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'active_tasks' => Job::where('technician_id', $user->id)
                ->where('status', 'process')
                ->count(),
            'monthly_attendance' => Presence::where('user_id', $user->id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count(),
        ];

        return view('karyawan.profile', compact('stats'));
    }
}
