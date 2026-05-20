<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobTracker;
use App\Models\JobComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function create()
    {
        if (in_array(Auth::user()->role, ['kepala', 'admin'], true)) {
            $technicians = User::where('role', 'karyawan')->with('division')->orderBy('name')->get();
            $jobs = Job::with(['cs', 'technician.division'])->latest()->get();
        } else {
            $technicians = User::where('role', 'karyawan')
                ->with('division')
                ->orderBy('name')
                ->get();
            $jobs = Job::with(['cs', 'technician.division'])
                ->where('cs_id', Auth::id())
                ->latest()
                ->get();
        }

        return view('cs.jobs.create', compact('technicians', 'jobs'));
    }

    public function index()
    {
        $jobs = Job::with(['cs', 'technician.division', 'trackers', 'comments.user'])
            ->latest()
            ->get();

        return view('jobs.history', compact('jobs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'technician_id' => 'required|exists:users,id',
            'client_name' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:32',
            'location' => 'nullable|string|max:1000',
            'google_maps_link' => 'nullable|url|max:2048',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
        ]);

        Job::create([
            'title'          => $request->title,
            'description'    => $request->description,
            'cs_id'          => Auth::id(),
            'technician_id'  => $request->technician_id,
            'status'         => 'pending',
            'client_name'    => $data['client_name'] ?? null,
            'whatsapp_number'=> $data['whatsapp_number'] ?? null,
            'location'       => $data['location'] ?? null,
            'google_maps_link' => $data['google_maps_link'] ?? null,
            'start_time'     => $data['start_time'] ?? null,
            'end_time'       => $data['end_time'] ?? null,
        ]);
        return redirect()->back()->with('success', 'Tugas berhasil dikirim ke Teknisi!');
    }

    public function updateProgress(Request $request, Job $job)
    {
        $request->validate([
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|mimes:jpeg,jpg,png,webp,heic,heif|max:5120',
            'video'       => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/3gpp|max:20480',
        ]);

        $photoPath = null;
        $videoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('job_photos', 'public');
        }

        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('job_videos', 'public');
        }

        \App\Models\JobTracker::create([
            'job_id'            => $job->id,
            'step_number'       => $job->current_step,
            'description_value' => $request->description,
            'photo_path'        => $photoPath,
            'video_path'        => $videoPath,
        ]);

        $job->increment('current_step');

        if ($job->current_step > 4) {
            $completedAt = now();
            $actualDuration = $job->accepted_at
                ? $job->accepted_at->diffInMinutes($completedAt)
                : null;

            $job->update([
                'status' => 'completed',
                'completed_at' => $completedAt,
                'actual_duration' => $actualDuration,
            ]);

            return redirect()->route('technician.dashboard')->with('success', 'Tugas Selesai!');
        }

        return back()->with('success', 'Tahap ' . ($job->current_step - 1) . ' berhasil disimpan!');
    }

    public function technicianDashboard()
    {
        $jobs = Job::where('technician_id', Auth::id())
                    ->where('status', '!=', 'completed')
                    ->with(['cs', 'trackers'])
                    ->latest()
                    ->get();

        $todayPresence = \App\Models\Presence::where('user_id', Auth::id())
            ->whereDate('date', now()->toDateString())
            ->checkInRecords()
            ->latest()
            ->first();

        return view('technician.dashboard', compact('jobs', 'todayPresence'));
    }

    public function acceptJob(Job $job)
    {
        $job->update([
            'status' => 'process',
            'current_step' => 1,
            'accepted_at' => now(),
        ]);

        return back()->with('success', 'Tugas diambil! Silakan mulai tracker.');
    }

    /**
     * Riwayat tugas - visible untuk semua role
     * - kepala: semua tugas
     * - karyawan: tugas yang dia buat (cs) ATAU dia kerjakan (technician)
     */
    public function history()
{
    // Semua user (Kepala, CS, Teknisi) bisa melihat semua riwayat
    $jobs = Job::with(['cs', 'technician.division', 'trackers', 'comments.user'])
                ->latest()
                ->get();

    return view('jobs.history', compact('jobs'));
}

    public function timeline()
    {
        $jobs = Job::with(['cs', 'technician.division', 'trackers'])
            ->latest()
            ->get();

        $timelineGroups = $jobs->groupBy(fn ($job) => $job->created_at?->format('Y-m-d') ?? 'tanpa-tanggal');
        $statusCounts = [
            'pending' => $jobs->where('status', 'pending')->count(),
            'process' => $jobs->where('status', 'process')->count(),
            'completed' => $jobs->where('status', 'completed')->count(),
            'overdue' => $jobs->filter->is_overdue->count(),
        ];

        return view('jobs.timeline', compact('jobs', 'timelineGroups', 'statusCounts'));
    }

    public function storeFeedback(Request $request, Job $job)
    {
        $request->validate([
            'feedback' => 'required|string'
        ]);

        $job->update(['feedback' => $request->feedback]);

        return back()->with('success', 'Feedback berhasil disimpan!');
    }

    /**
     * Simpan komentar dari semua karyawan
     */
    public function storeComment(Request $request, Job $job)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        JobComment::create([
            'job_id'  => $job->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Komentar berhasil ditambahkan!');
    }

    /**
     * Hapus komentar (hanya pemilik atau kepala)
     */
    public function destroyComment(JobComment $comment)
    {
        $user = Auth::user();
        if ($user->role === 'kepala' || $comment->user_id === $user->id) {
            $comment->delete();
            return back()->with('success', 'Komentar dihapus.');
        }
        return back()->with('error', 'Tidak diizinkan.');
    }
}
