<x-app-layout>
    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Roadmap Tugas</h2>
                        <p class="admin-subtitle">Timeline berdasarkan tanggal tugas dibuat, seperti roadmap pekerjaan harian.</p>
                    </div>

                    <a href="{{ route('jobs.create') }}" class="btn-primary-soft">
                        <i class="fas fa-plus mr-2"></i> Buat Tugas
                    </a>
                </div>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Menunggu</p>
                    <p class="mt-3 text-2xl font-bold text-slate-950">{{ $statusCounts['pending'] }}</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Berlangsung</p>
                    <p class="mt-3 text-2xl font-bold text-slate-950">{{ $statusCounts['process'] }}</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Selesai</p>
                    <p class="mt-3 text-2xl font-bold text-slate-950">{{ $statusCounts['completed'] }}</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Overdue</p>
                    <p class="mt-3 text-2xl font-bold text-slate-950">{{ $statusCounts['overdue'] }}</p>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Timeline Tanggal</h3>
                    <p class="mt-1 text-xs text-slate-500">Setiap tanggal berisi daftar tugas yang dibuat pada hari tersebut.</p>
                </div>

                <div class="p-5 sm:p-6">
                    @forelse($timelineGroups as $date => $jobsByDate)
                        @php
                            $groupDate = $date === 'tanpa-tanggal' ? null : \Carbon\Carbon::parse($date);
                            $dateLabel = $groupDate ? $groupDate->format('d M Y') : 'Tanpa Tanggal';
                            $dayLabel = $groupDate ? $groupDate->translatedFormat('l') : '-';
                            $pending = $jobsByDate->where('status', 'pending')->count();
                            $process = $jobsByDate->where('status', 'process')->count();
                            $completed = $jobsByDate->where('status', 'completed')->count();
                        @endphp

                        <div class="relative pb-10 last:pb-0">
                            @if(!$loop->last)
                                <div class="absolute left-6 top-14 h-[calc(100%-34px)] w-px bg-slate-200"></div>
                            @endif

                            <div class="flex gap-4">
                                <div class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                                    <i class="fas fa-calendar-day"></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="mb-4 flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <h4 class="text-lg font-bold text-slate-950">{{ $dateLabel }}</h4>
                                            <p class="mt-1 text-sm text-slate-500">{{ $dayLabel }} · {{ $jobsByDate->count() }} tugas dibuat</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="app-badge-warning">{{ $pending }} pending</span>
                                            <span class="app-badge bg-sky-100 text-sky-700">{{ $process }} proses</span>
                                            <span class="app-badge-success">{{ $completed }} selesai</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                                        @foreach($jobsByDate as $job)
                                            <article class="app-surface p-5">
                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="text-xs font-semibold text-slate-500">
                                                                {{ $job->created_at ? $job->created_at->format('H:i') : '--:--' }}
                                                            </span>
                                                            <span class="app-badge {{ $job->status === 'completed' ? 'app-badge-success' : ($job->status === 'process' ? 'bg-sky-100 text-sky-700' : 'app-badge-warning') }}">
                                                                {{ $job->status }}
                                                            </span>
                                                        </div>
                                                        <h5 class="mt-2 text-base font-semibold text-slate-950">{{ $job->title }}</h5>
                                                        <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-600">{{ $job->description ?: 'Tidak ada detail tugas.' }}</p>
                                                    </div>
                                                    <div class="rounded-lg bg-slate-100 px-3 py-2 text-center">
                                                        <p class="text-xs font-semibold text-slate-500">Step</p>
                                                        <p class="text-lg font-bold text-slate-950">{{ $job->current_step }}</p>
                                                    </div>
                                                </div>

                                                <div class="mt-4 grid grid-cols-1 gap-3 border-t border-slate-100 pt-4 text-xs text-slate-500 sm:grid-cols-2">
                                                    <div>
                                                        <span class="block font-semibold text-slate-700">Client</span>
                                                        {{ $job->client_name ?: '-' }}
                                                    </div>
                                                    <div>
                                                        <span class="block font-semibold text-slate-700">WhatsApp</span>
                                                        @if($job->whatsapp_url)
                                                            <a href="{{ $job->whatsapp_url }}" target="_blank" class="font-semibold text-emerald-700 hover:text-emerald-900">{{ $job->whatsapp_number }}</a>
                                                        @else
                                                            -
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <span class="block font-semibold text-slate-700">Teknisi</span>
                                                        {{ $job->technician->name ?? '-' }}
                                                    </div>
                                                    <div>
                                                        <span class="block font-semibold text-slate-700">Mulai</span>
                                                        {{ $job->start_time ? $job->start_time->format('d M Y H:i') : '-' }}
                                                    </div>
                                                    <div>
                                                        <span class="block font-semibold text-slate-700">Deadline</span>
                                                        {{ $job->end_time ? $job->end_time->format('d M Y H:i') : '-' }}
                                                    </div>
                                                </div>

                                                @if($job->location || $job->maps_url)
                                                    <div class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-xs leading-5 text-slate-500">
                                                        <i class="fas fa-location-dot mr-1 text-slate-400"></i>{{ $job->location ?: 'Alamat belum diisi.' }}
                                                        @if($job->maps_url)
                                                            <a href="{{ $job->maps_url }}" target="_blank" class="ml-2 font-semibold text-emerald-700 hover:text-emerald-900">Maps</a>
                                                        @endif
                                                    </div>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                <i class="fas fa-timeline"></i>
                            </div>
                            <p class="mt-3 font-semibold text-slate-900">Belum ada roadmap</p>
                            <p class="mt-1 text-sm text-slate-500">Roadmap akan muncul setelah tugas dibuat.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
