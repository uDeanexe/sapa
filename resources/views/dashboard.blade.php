<x-app-layout>
    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Dashboard Rangkuman Kerja</h2>
                        <p class="admin-subtitle">Pantau pekerjaan H-1, pekerjaan hari ini, dan tugas yang sedang berjalan.</p>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700">
                        {{ now()->format('d M Y') }}
                    </div>
                </div>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="metric-card metric-emerald">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Kerjaan Hari Ini</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <i class="fas fa-calendar-day"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['today_jobs'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Tugas dibuat hari ini</p>
                </div>

                <div class="metric-card metric-sky">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Kerjaan H-1</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                            <i class="fas fa-clock-rotate-left"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['yesterday_jobs'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Tugas dibuat kemarin</p>
                </div>

                <div class="metric-card metric-amber">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Sedang Berjalan</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <i class="fas fa-spinner"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['process_jobs'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Tugas dalam proses</p>
                </div>

                <div class="metric-card metric-rose">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Butuh Approval</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                            <i class="fas fa-clipboard-check"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary['pending_presence_approvals'] + $summary['pending_leaves'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Absensi dan perizinan</p>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="admin-card xl:col-span-2">
                    <div class="admin-card-header flex items-center justify-between gap-3">
                        <div>
                            <h3 class="admin-card-title">Pekerjaan Sekarang</h3>
                            <p class="mt-1 text-xs text-slate-500">Tugas pending dan proses yang perlu dipantau.</p>
                        </div>
                        <a href="{{ route('jobs.timeline') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">Lihat timeline</a>
                    </div>

                    <div class="app-table-wrap rounded-none border-0 shadow-none">
                        <table class="data-table admin-table-fixed">
                            <colgroup>
                                <col class="w-[28%]">
                                <col class="w-[18%]">
                                <col class="w-[22%]">
                                <col class="w-[14%]">
                                <col class="w-[18%]">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Tugas</th>
                                    <th>Client</th>
                                    <th>Teknisi</th>
                                    <th>Status</th>
                                    <th>Deadline</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeJobs as $job)
                                    <tr class="data-table-row">
                                        <td>
                                            <div class="font-semibold text-slate-950">{{ $job->title }}</div>
                                            <div class="admin-table-text mt-1 text-xs">{{ $job->description ?: '-' }}</div>
                                        </td>
                                        <td class="text-slate-700">{{ $job->client_name ?: '-' }}</td>
                                        <td>
                                            <div class="font-medium text-slate-800">{{ $job->technician->name ?? '-' }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $job->technician->division->name ?? 'Tanpa Divisi' }}</div>
                                        </td>
                                        <td>
                                            <span class="app-badge {{ $job->status === 'process' ? 'bg-sky-100 text-sky-700' : 'app-badge-warning' }}">
                                                {{ $job->status }}
                                            </span>
                                            <div class="mt-1 text-xs text-slate-500">Step {{ $job->current_step }}</div>
                                        </td>
                                        <td class="text-slate-700">
                                            {{ $job->end_time ? $job->end_time->format('d M Y H:i') : '-' }}
                                            @if($job->is_overdue)
                                                <div class="mt-1 text-xs font-semibold text-rose-600">Overdue</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="app-empty-state">
                                                <div class="app-empty-state-icon"><i class="fas fa-briefcase"></i></div>
                                                <p class="mt-3 font-semibold text-slate-900">Tidak ada pekerjaan aktif sekarang</p>
                                                <p class="mt-1 text-sm text-slate-500">Tugas pending dan proses akan muncul di sini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">Status Kerja</h3>
                        </div>
                        <div class="space-y-4 p-5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Pending</span>
                                <span class="app-badge-warning">{{ $summary['pending_jobs'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Proses</span>
                                <span class="app-badge bg-sky-100 text-sky-700">{{ $summary['process_jobs'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Selesai</span>
                                <span class="app-badge-success">{{ $summary['completed_jobs'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Overdue</span>
                                <span class="app-badge bg-rose-100 text-rose-700">{{ $summary['overdue_jobs'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">Operasional Hari Ini</h3>
                        </div>
                        <div class="space-y-4 p-5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Karyawan</span>
                                <span class="font-semibold text-slate-950">{{ $summary['employees'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Presensi Hari Ini</span>
                                <span class="font-semibold text-slate-950">{{ $summary['today_attendance'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Approval Absensi</span>
                                <a href="{{ route('admin.presence.index') }}" class="font-semibold text-emerald-700 hover:text-emerald-900">{{ $summary['pending_presence_approvals'] }}</a>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500">Perizinan Pending</span>
                                <a href="{{ route('admin.perizinan') }}" class="font-semibold text-emerald-700 hover:text-emerald-900">{{ $summary['pending_leaves'] }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Kerjaan Hari Ini</h3>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($todayJobs as $job)
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-slate-950">{{ $job->title }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $job->client_name ?: 'Tanpa client' }} · {{ $job->technician->name ?? '-' }}</p>
                                    </div>
                                    <span class="app-badge {{ $job->status === 'completed' ? 'app-badge-success' : ($job->status === 'process' ? 'bg-sky-100 text-sky-700' : 'app-badge-warning') }}">{{ $job->status }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                <div class="app-empty-state-icon"><i class="fas fa-calendar-day"></i></div>
                                <p class="mt-3 font-semibold text-slate-900">Belum ada tugas hari ini</p>
                                <p class="mt-1 text-sm text-slate-500">Tugas yang dibuat hari ini akan muncul di daftar ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Kerjaan H-1</h3>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($yesterdayJobs as $job)
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-slate-950">{{ $job->title }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $job->client_name ?: 'Tanpa client' }} · {{ $job->technician->name ?? '-' }}</p>
                                    </div>
                                    <span class="app-badge {{ $job->status === 'completed' ? 'app-badge-success' : ($job->status === 'process' ? 'bg-sky-100 text-sky-700' : 'app-badge-warning') }}">{{ $job->status }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                <div class="app-empty-state-icon"><i class="fas fa-clock-rotate-left"></i></div>
                                <p class="mt-3 font-semibold text-slate-900">Tidak ada tugas H-1</p>
                                <p class="mt-1 text-sm text-slate-500">Tugas yang dibuat kemarin akan muncul di daftar ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
