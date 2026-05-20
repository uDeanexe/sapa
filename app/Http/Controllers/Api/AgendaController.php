<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyChecklist;
use App\Models\FormTemplate;
use App\Models\Holiday;
use App\Models\Job;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('division');
        $scope = $request->query('scope') === 'week' ? 'week' : 'today';
        $from = now()->startOfDay();
        $to = $scope === 'week'
            ? now()->addDays(6)->endOfDay()
            : now()->endOfDay();

        $agendas = $this->buildAgendas($user->id, $from, $to);
        $tasks = $this->buildTasks($user, $from, $to);
        $reminder = $tasks
            ->filter(fn ($task) => $task['status'] !== 'Selesai')
            ->sortBy('due_time')
            ->first();

        return response()->json([
            'success' => true,
            'period' => [
                'scope' => $scope,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'summary' => [
                'agenda_count' => $agendas->count(),
                'task_count' => $tasks->count(),
                'urgent_count' => $tasks->where('priority', 'Tinggi')->count(),
            ],
            'reminder' => $reminder ? array_intersect_key($reminder, array_flip([
                'id',
                'type',
                'title',
                'due_time',
                'priority',
                'status',
            ])) : null,
            'agendas' => $agendas->values(),
            'tasks' => $tasks->values(),
        ]);
    }

    private function buildAgendas(int $userId, Carbon $from, Carbon $to): Collection
    {
        $holidays = Holiday::whereBetween('holiday_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('holiday_date')
            ->get()
            ->map(fn ($holiday) => [
                'id' => 'holiday-'.$holiday->id,
                'type' => 'holiday',
                'title' => $holiday->name ?? 'Libur Kantor',
                'description' => 'Tanggal ini ditandai sebagai hari libur kantor.',
                'start_time' => Carbon::parse($holiday->holiday_date)->startOfDay()->toIso8601String(),
                'end_time' => Carbon::parse($holiday->holiday_date)->endOfDay()->toIso8601String(),
                'location' => 'Kalender kantor',
                'status' => 'Libur',
                'participants' => ['Semua karyawan'],
                'color' => 'red',
            ]);

        $leaves = Leave::where('user_id', $userId)
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_date', [$from->toDateString(), $to->toDateString()])
                    ->orWhereBetween('end_date', [$from->toDateString(), $to->toDateString()])
                    ->orWhere(function ($nested) use ($from, $to) {
                        $nested->where('start_date', '<=', $from->toDateString())
                            ->where('end_date', '>=', $to->toDateString());
                    });
            })
            ->orderBy('start_date')
            ->get()
            ->map(fn ($leave) => [
                'id' => 'leave-'.$leave->id,
                'type' => 'leave',
                'title' => 'Pengajuan '.ucfirst($leave->type ?? 'izin'),
                'description' => $leave->reason ?? 'Tidak ada catatan.',
                'start_time' => Carbon::parse($leave->start_date)->startOfDay()->toIso8601String(),
                'end_time' => Carbon::parse($leave->end_date ?? $leave->start_date)->endOfDay()->toIso8601String(),
                'location' => 'Absensi',
                'status' => ucfirst($leave->status ?? 'pending'),
                'participants' => ['Saya'],
                'color' => match ($leave->status) {
                    'approved' => 'green',
                    'rejected' => 'red',
                    default => 'amber',
                },
            ]);

        return collect($holidays->all())
            ->merge($leaves->all())
            ->sortBy('start_time');
    }

    private function buildTasks($user, Carbon $from, Carbon $to): Collection
    {
        $jobs = Job::with(['technician', 'cs', 'trackers'])
            ->where(function ($query) use ($user) {
                $query->where('technician_id', $user->id)
                    ->orWhere('cs_id', $user->id);
            })
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_time', [$from, $to])
                    ->orWhereBetween('end_time', [$from, $to])
                    ->orWhere(function ($nested) {
                        $nested->whereNull('start_time')
                            ->where('status', '!=', 'completed');
                    })
                    ->orWhere(function ($nested) use ($from, $to) {
                        $nested->where('start_time', '<=', $from)
                            ->where('end_time', '>=', $to);
                    });
            })
            ->latest('start_time')
            ->get()
            ->map(fn ($job) => $this->formatJobTask($job, $user->id));

        return collect($jobs->all())
            ->merge($this->buildChecklistTasks($user, $from, $to))
            ->sortBy('due_time')
            ->values();
    }

    private function formatJobTask(Job $job, int $userId): array
    {
        $due = $job->end_time ?? $job->start_time ?? $job->created_at;
        $completedSteps = $job->trackers->pluck('step_number')->unique()->count();
        $progress = $job->status === 'completed'
            ? 1
            : min(0.95, max(0, $completedSteps / 4));
        $isOwner = (int) $job->technician_id === $userId;

        return [
            'id' => 'job-'.$job->id,
            'source_id' => $job->id,
            'type' => 'job',
            'title' => $job->title,
            'description' => $job->description ?: ($job->client_name ? 'Klien: '.$job->client_name : 'Tugas pekerjaan'),
            'assignee' => $isOwner ? 'Saya' : ($job->technician->name ?? '-'),
            'due_time' => $due?->toIso8601String(),
            'priority' => $this->jobPriority($job, $due),
            'status' => match ($job->status) {
                'completed' => 'Selesai',
                'process' => 'Proses',
                default => 'Belum mulai',
            },
            'progress' => $progress,
            'location' => $job->location,
            'whatsapp_number' => $job->whatsapp_number,
            'whatsapp_url' => $job->whatsapp_url,
            'google_maps_link' => $job->google_maps_link,
            'maps_url' => $job->maps_url,
            'route' => 'job_detail',
            'route_id' => (string) $job->id,
            'color' => match ($job->status) {
                'completed' => 'green',
                'process' => 'blue',
                default => 'amber',
            },
        ];
    }

    private function jobPriority(Job $job, ?Carbon $due): string
    {
        if ($job->status !== 'completed' && $due && now()->greaterThan($due)) {
            return 'Tinggi';
        }

        // Menjadikan pekerjaan H-1 (besok) dan hari ini sebagai prioritas Tinggi
        if ($due && ($due->isToday() || $due->isTomorrow())) {
            return 'Tinggi';
        }

        return $job->status === 'process' ? 'Sedang' : 'Normal';
    }

    private function buildChecklistTasks($user, Carbon $from, Carbon $to): Collection
    {
        if (! $user->division_id) {
            return collect();
        }

        $templates = FormTemplate::where('division_id', $user->division_id)
            ->get(['id', 'tipe_form']);

        if ($templates->isEmpty()) {
            return collect();
        }

        $filled = DailyChecklist::where('user_id', $user->id)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->get(['date', 'tipe_form'])
            ->mapWithKeys(fn ($item) => [
                Carbon::parse($item->date)->toDateString().'|'.$item->tipe_form => true,
            ]);

        $items = collect();
        for ($date = $from->copy()->startOfDay(); $date->lte($to); $date->addDay()) {
            foreach ($templates as $template) {
                $due = $this->checklistDueTime($date, $template->tipe_form);
                $key = $date->toDateString().'|'.$template->tipe_form;
                $done = $filled->has($key);

                $items->push([
                    'id' => 'checklist-'.$template->id.'-'.$date->toDateString(),
                    'source_id' => $template->id,
                    'type' => 'checklist',
                    'title' => 'Checklist '.$template->tipe_form,
                    'description' => 'Isi checklist '.$template->tipe_form.' untuk divisi '.$user->division?->name.'.',
                    'assignee' => 'Saya',
                    'due_time' => $due->toIso8601String(),
                    'priority' => ! $done && $due->isPast() ? 'Tinggi' : 'Normal',
                    'status' => $done ? 'Selesai' : 'Belum diisi',
                    'progress' => $done ? 1 : 0,
                    'location' => 'Checklist harian',
                    'route' => 'checklist',
                    'route_id' => $date->toDateString(),
                    'color' => $done ? 'green' : 'purple',
                ]);
            }
        }

        return $items;
    }

    private function checklistDueTime(Carbon $date, string $type): Carbon
    {
        $hour = str_contains(strtolower($type), 'pulang') || str_contains(strtolower($type), 'sore')
            ? 17
            : 10;

        return $date->copy()->setTime($hour, 0);
    }
}
