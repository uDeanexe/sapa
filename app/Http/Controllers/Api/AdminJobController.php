<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class AdminJobController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless(in_array((string) ($user->role ?? ''), ['admin', 'kepala'], true), 403);

        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));

        $query = Job::with(['cs:id,name', 'technician:id,name,division_id', 'technician.division:id,name'])
            ->withCount(['trackers', 'comments'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if (in_array($status, ['pending', 'process', 'completed'], true)) {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', '%'.$q.'%')
                    ->orWhere('client_name', 'like', '%'.$q.'%')
                    ->orWhere('location', 'like', '%'.$q.'%');
            });
        }

        $limit = (int) $request->query('limit', 30);
        $limit = max(5, min(100, $limit));

        $jobs = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $jobs->map(fn (Job $job) => $this->formatJob($job))->values(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless(in_array((string) ($user->role ?? ''), ['admin', 'kepala'], true), 403);

        $job = Job::with([
            'cs:id,name',
            'technician:id,name,division_id',
            'technician.division:id,name',
            'trackers',
            'comments.user:id,name',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'job' => $this->formatJob($job),
        ]);
    }

    private function formatJob(Job $job): array
    {
        $trackers = ($job->trackers ?? collect())->map(fn ($t) => [
            'id' => $t->id,
            'step_number' => (int) $t->step_number,
            'description_value' => $t->description_value,
            'photo_path' => $t->photo_path,
            'video_path' => $t->video_path,
            'created_at' => $t->created_at?->toIso8601String(),
        ])->values()->toArray();

        $comments = ($job->comments ?? collect())->map(fn ($c) => [
            'id' => $c->id,
            'comment' => $c->comment,
            'user_id' => (int) $c->user_id,
            'user_name' => $c->user?->name ?? '-',
            'created_at' => $c->created_at?->toIso8601String(),
        ])->values()->toArray();

        return [
            'id' => $job->id,
            'title' => $job->title,
            'description' => $job->description,
            'status' => $job->status,
            'current_step' => $job->status === 'completed' ? null : (int) ($job->current_step ?? 1),
            'cs' => $job->cs ? ['id' => $job->cs->id, 'name' => $job->cs->name] : null,
            'technician' => $job->technician ? ['id' => $job->technician->id, 'name' => $job->technician->name] : null,
            'technician_id' => $job->technician_id,
            'client_name' => $job->client_name,
            'whatsapp_number' => $job->whatsapp_number,
            'location' => $job->location,
            'google_maps_link' => $job->google_maps_link,
            'start_time' => $job->start_time?->toIso8601String(),
            'end_time' => $job->end_time?->toIso8601String(),
            'accepted_at' => $job->accepted_at?->toIso8601String(),
            'completed_at' => $job->completed_at?->toIso8601String(),
            'actual_duration' => $job->actual_duration !== null ? (int) $job->actual_duration : null,
            'completion_reason' => $job->completion_reason,
            'trackers_count' => (int) ($job->trackers_count ?? count($trackers)),
            'comments_count' => (int) ($job->comments_count ?? count($comments)),
            'trackers' => $trackers,
            'comments' => $comments,
            'created_at' => $job->created_at?->toIso8601String(),
        ];
    }
}

