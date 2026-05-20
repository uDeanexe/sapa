<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobTracker;
use App\Models\JobComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InternalNotification;

class JobApiController extends Controller
{
    public function getActiveJobs()
    {
        $jobs = Job::with(['cs', 'technician.division', 'trackers', 'comments.user'])
            ->where('status', '!=', 'completed')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $this->formatJobs($jobs)
        ]);
    }

    public function show($id)
    {
        $job = Job::with(['cs', 'technician.division', 'trackers', 'comments.user'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'job' => $this->formatJob($job)
        ]);
    }

    public function getJobHistory()
    {
        $jobs = Job::with(['cs', 'technician.division', 'trackers', 'comments.user'])
            ->where('status', 'completed')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $this->formatJobs($jobs)
        ]);
    }

    public function acceptJob(Request $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        if ($job->technician_id !== $request->user()->id) {
            return response()->json(['error' => 'Bukan tugas Anda'], 403);
        }

        $job->update([
            'status'       => 'process',
            'current_step' => 1,   
            'accepted_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diambil!',
            'job'     => $this->formatJob($job->load(['cs', 'technician.division', 'trackers', 'comments.user'])),
        ]);
    }

    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'description_value' => 'nullable|string|max:5000',
            'completion_reason' => 'nullable|string|max:5000',
            'photo' => 'nullable|image|max:5120',
            'video' => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:20480',
        ]);

        $job  = Job::with('technician.division')->findOrFail($id);
        $user = auth()->user();

        if ($job->technician_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Hanya teknisi pelaksana yang bisa update'], 403);
        }

        $stepToSave = $job->current_step ?? 1;
        if ($job->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Tugas sudah selesai'], 400);
        }

        if ($stepToSave > 4) {
            return response()->json(['success' => false, 'message' => 'Tugas sudah selesai'], 400);
        }

        $existingTracker = JobTracker::where('job_id', $job->id)
            ->where('step_number', $stepToSave)
            ->exists();

        if ($existingTracker) {
            return response()->json([
                'success' => false,
                'message' => "Tahap {$stepToSave} sudah pernah disimpan"
            ], 400);
        }

        $division       = $job->technician?->division;
        $reqPhoto       = $division ? (bool) $division->{"req_photo_{$stepToSave}"} : false;
        $reqVideo       = $division ? (bool) $division->{"req_video_{$stepToSave}"} : false;
        $reqDescription = $division ? (bool) $division->{"req_desc_{$stepToSave}"}  : false;

        if ($reqDescription && empty(trim($request->description_value ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => "Deskripsi wajib diisi untuk Tahap {$stepToSave}."
            ], 422);
        }

        if ($reqPhoto && !$request->hasFile('photo')) {
            return response()->json([
                'success' => false,
                'message' => "Foto bukti wajib dilampirkan untuk Tahap {$stepToSave}."
            ], 422);
        }

        if ($reqVideo && !$request->hasFile('video')) {
            return response()->json([
                'success' => false,
                'message' => "Video bukti wajib dilampirkan untuk Tahap {$stepToSave}."
            ], 422);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            // Menggunakan store() untuk best practice dan keamanan Laravel
            $photoPath = $request->file('photo')->store('job_photos', 'public');
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            // Menggunakan store() untuk best practice dan keamanan Laravel
            $videoPath = $request->file('video')->store('job_videos', 'public');
        }

        // ── Buat tracker dengan step_number = step yang baru diselesaikan ───
        JobTracker::create([
            'job_id'            => $job->id,
            'step_number'       => $stepToSave,
            'description_value' => $request->description_value,
            'photo_path'        => $photoPath,
            'video_path'        => $videoPath,
        ]);

        $isCompleted = ($stepToSave >= 4);

        $actualDuration = null;
        if ($isCompleted && $job->accepted_at) {
            $acceptedAt     = \Carbon\Carbon::parse($job->accepted_at);
            $actualDuration = (int) $acceptedAt->diffInMinutes(now());
        }

        $job->update([
            'current_step'      => $isCompleted ? 4 : ($stepToSave + 1),
            'status'            => $isCompleted ? 'completed' : 'process',
            'completed_at'      => $isCompleted ? now() : null,
            'actual_duration'   => $actualDuration,
            'completion_reason' => $request->completion_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Tahap {$stepToSave} berhasil disimpan" . ($isCompleted ? '. Tugas selesai!' : ''),
            'job'     => $this->formatJob($job->load(['trackers', 'cs', 'technician.division', 'comments.user']))
        ]);
    }

    public function getTechnicians(Request $request)
    {
        $query = User::with('division')->where('role', 'karyawan');

        $technicians = $query->get()->map(function ($u) {
            return [
                'id'       => $u->id,
                'name'     => $u->name,
                'division' => $u->division ? $u->division->name : '-',
            ];
        });

        return response()->json(['success' => true, 'data' => $technicians]);
    }

    public function createJob(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'technician_id' => 'required|exists:users,id',
            'client_name'   => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:32',
            'location'      => 'nullable|string',
            'google_maps_link' => 'nullable|url|max:2048',
            'start_time'    => 'nullable|date',
            'end_time'      => 'nullable|date',
        ]);

        $job = Job::create([
            'title'         => $request->title,
            'description'   => $request->description,
            'cs_id'         => auth()->id(),
            'technician_id' => $request->technician_id,
            'status'        => 'pending',
            'client_name'   => $request->client_name,
            'whatsapp_number' => $request->whatsapp_number,
            'location'      => $request->location,
            'google_maps_link' => $request->google_maps_link,
            'start_time'    => $request->start_time,
            'end_time'      => $request->end_time,
        ]);

        $receiver = User::find($request->technician_id);
        if ($receiver) {
            $estInfo = '';
            if ($request->start_time && $request->end_time) {
                $start   = \Carbon\Carbon::parse($request->start_time)->format('d M H:i');
                $end     = \Carbon\Carbon::parse($request->end_time)->format('d M H:i');
                $estInfo = " | $start – $end";
            }
            $receiver->notify(new \App\Notifications\InternalNotification([
                'title'    => 'Tugas Baru!',
                'message'  => 'Anda mendapatkan tugas: ' . $request->title . $estInfo,
                'type'     => 'job_assigned',
                'route'    => 'job_detail',
                'route_id' => $job->id,
            ]));
        }

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dibuat!',
            'job'     => $this->formatJob($job->load(['cs', 'technician.division', 'trackers', 'comments.user']))
        ], 201);
    }

    public function addComment(Request $request, $jobId)
    {
        $request->validate(['comment' => 'required|string|max:1000']);

        $comment = JobComment::create([
            'job_id'  => $jobId,
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'comment' => [
                'id'         => $comment->id,
                'comment'    => $comment->comment,
                'user_name'  => auth()->user()->name,
                'user_id'    => auth()->id(),
                'created_at' => $comment->created_at->format('d M Y H:i'),
            ],
        ], 201);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function formatJobs($jobs): array
    {
        return $jobs->map(fn($j) => $this->formatJob($j))->values()->toArray();
    }

    private function formatJob(Job $job): array
    {
        $trackers = ($job->trackers ?? collect())->map(fn($t) => [
            'id'                => $t->id,
            'step_number'       => (int) $t->step_number,
            'description_value' => $t->description_value,
            'photo'             => $this->publicStorageUrl($t->photo_path),
            'video'             => $this->publicStorageUrl($t->video_path),
            'photo_url'         => $this->publicStorageUrl($t->photo_path),
            'video_url'         => $this->publicStorageUrl($t->video_path),
            'created_at'        => $t->created_at?->format('d M Y H:i'),
        ])->values()->toArray();

        $comments = ($job->comments ?? collect())->map(fn($c) => [
            'id'         => $c->id,
            'comment'    => $c->comment,
            'user_name'  => $c->user->name ?? '-',
            'user_id'    => $c->user_id,
            'created_at' => $c->created_at?->format('d M Y H:i'),
        ])->values()->toArray();

        // Hitung status overdue
        $isOverdue = false;
        if ($job->end_time && $job->accepted_at) {
            $deadline  = \Carbon\Carbon::parse($job->end_time);
            $reference = $job->completed_at
                ? \Carbon\Carbon::parse($job->completed_at)
                : now();
            $isOverdue = $reference->isAfter($deadline);
        }

        // Ambil persyaratan divisi teknisi per tahap
        $division         = $job->technician?->division;
        $stepRequirements = [];
        for ($i = 1; $i <= 4; $i++) {
            $stepRequirements[$i] = [
                'step_name' => $division ? ($division->{"step_{$i}"} ?? "Tahap {$i}") : "Tahap {$i}",
                'req_desc'  => $division ? (bool) $division->{"req_desc_{$i}"}  : false,
                'req_photo' => $division ? (bool) $division->{"req_photo_{$i}"} : false,
                'req_video' => $division ? (bool) $division->{"req_video_{$i}"} : false,
            ];
        }

        // ── FIX: completed_steps = jumlah tracker unik per step ────────────
        // Deduplikasi agar step 4 yang ter-submit 2x tidak dihitung 2x
        $completedSteps = collect($trackers)->pluck('step_number')->unique()->count();

        // ── FIX: current_step untuk INPUT ──────────────────────────────────
        // Jika completed → tidak ada form input lagi
        // Jika process   → ambil dari DB (sudah di-update setelah submit)
        // Jika pending   → belum mulai
        if ($job->status === 'completed') {
            $currentStepInput = null; // Flutter: tidak tampil form
        } else {
            $currentStepInput = (int) ($job->current_step ?? 1);
        }

        return [
            'id'                => $job->id,
            'title'             => $job->title,
            'description'       => $job->description,
            'status'            => $job->status,
            'current_step'      => $currentStepInput,
            'completed_steps'   => $completedSteps,
            'feedback'          => $job->feedback,
            'cs'                => $job->cs ? ['id' => $job->cs->id, 'name' => $job->cs->name] : null,
            'technician'        => $job->technician
                ? ['id' => $job->technician->id, 'name' => $job->technician->name]
                : null,
            'technician_id'     => $job->technician_id,
            'trackers'          => $trackers,
            'comments'          => $comments,
            'is_completed'      => $job->status === 'completed',
            'is_process'        => $job->status === 'process',
            'is_pending'        => $job->status === 'pending',
            'created_at'        => $job->created_at?->format('d M Y'),

            // Lokasi & waktu
            'client_name'       => $job->client_name,
            'whatsapp_number'   => $job->whatsapp_number,
            'whatsapp_url'      => $job->whatsapp_url,
            'location'          => $job->location,
            'google_maps_link'  => $job->google_maps_link,
            'maps_url'          => $job->maps_url,
            'start_time'        => $job->start_time?->toIso8601String(),
            'end_time'          => $job->end_time?->toIso8601String(),
            'accepted_at'       => $job->accepted_at?->toIso8601String(),
            'completed_at'      => $job->completed_at?->toIso8601String(),
            // FIX: selalu int atau null, tidak pernah double
            'actual_duration'   => $job->actual_duration !== null ? (int) $job->actual_duration : null,
            'completion_reason' => $job->completion_reason,
            'is_overdue'        => $isOverdue,

            // Persyaratan divisi per tahap (untuk Flutter validasi dinamis)
            'step_requirements' => $stepRequirements,
        ];
    }

    private function publicStorageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        $path = str_starts_with($path, 'storage/') ? $path : 'storage/'.$path;
        $baseUrl = rtrim(request()->getSchemeAndHttpHost(), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
