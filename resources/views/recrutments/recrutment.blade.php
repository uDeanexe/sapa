<x-app-layout>
    @php
        $stages = ['Applied', 'Screening', 'Interview', 'Offering', 'Hired'];
        $stageColors = [
            'Applied' => 'bg-slate-600',
            'Screening' => 'bg-sky-600',
            'Interview' => 'bg-amber-500',
            'Offering' => 'bg-emerald-600',
            'Hired' => 'bg-indigo-600',
        ];
        $pipeline = collect($stages)->map(fn ($stage) => [
            'stage' => $stage,
            'count' => $candidates->where('stage', $stage)->count(),
            'color' => $stageColors[$stage],
            'items' => $candidates->where('stage', $stage)->take(3),
        ]);
        $openUrgent = $openings->where('status', 'Aktif')->where('priority', 'Tinggi')->count();
        $activeOpenings = $openings->where('status', 'Aktif')->count();
        $totalQuota = $openings->where('status', 'Aktif')->sum('quota');
        $hiredCount = $candidates->where('stage', 'Hired')->count();
        $slaPercent = $totalQuota > 0 ? min(100, round(($hiredCount / $totalQuota) * 100)) : 0;
    @endphp

    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Manajemen Rekrutmen</h2>
                        <p class="admin-subtitle">Kelola pipeline kandidat, tugas HR, dan progres hiring tiap posisi.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('recruitment.kandidat') }}" class="btn-secondary-soft">
                            <i class="fas fa-users mr-2"></i>
                            Kandidat
                        </a>
                        <a href="{{ route('recruitment.lowongan') }}" class="btn-primary-soft">
                            <i class="fas fa-briefcase mr-2"></i>
                            Lowongan
                        </a>
                    </div>
                </div>
            </div>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-5">
                @foreach($pipeline as $item)
                    <div class="metric-card {{ $loop->iteration === 1 ? '' : ($loop->iteration === 2 ? 'metric-sky' : ($loop->iteration === 3 ? 'metric-amber' : ($loop->iteration === 4 ? 'metric-emerald' : 'metric-indigo'))) }}">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-500">{{ $item['stage'] }}</p>
                            <span class="h-3 w-3 rounded-full {{ $item['color'] }}"></span>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-slate-950">{{ $item['count'] }}</p>
                        <p class="mt-1 text-xs text-slate-500">Kandidat</p>
                    </div>
                @endforeach
            </section>

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_380px]">
                <div class="app-surface p-5">
                    <h3 class="app-section-title">Pipeline Aktif</h3>
                    <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-5">
                        @foreach($pipeline as $item)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="mb-4 flex items-center justify-between">
                                    <p class="text-sm font-bold text-slate-900">{{ $item['stage'] }}</p>
                                    <span class="app-badge-muted">{{ $item['count'] }}</span>
                                </div>
                                <div class="space-y-3">
                                    @forelse($item['items'] as $candidate)
                                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                                            <p class="truncate text-sm font-semibold text-slate-900">{{ $candidate->name }}</p>
                                            <p class="mt-1 truncate text-xs text-slate-500">{{ $candidate->position }}</p>
                                        </div>
                                    @empty
                                        <div class="rounded-lg border border-dashed border-slate-200 bg-white p-3 text-xs leading-5 text-slate-500">Belum ada kandidat pada tahap ini.</div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="app-surface p-5">
                        <h3 class="app-section-title">Fokus Rekrutmen</h3>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="font-semibold text-slate-950">Lowongan aktif</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $activeOpenings }} posisi sedang dibuka.</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="font-semibold text-slate-950">Prioritas tinggi</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $openUrgent }} lowongan perlu dipercepat.</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="font-semibold text-slate-950">Kandidat interview</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $candidates->where('stage', 'Interview')->count() }} orang menunggu keputusan user.</p>
                            </div>
                        </div>
                    </div>

                    <div class="app-surface p-5">
                        <h3 class="app-section-title">Pemenuhan Kuota</h3>
                        <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-emerald-600" data-progress-width="{{ $slaPercent }}"></div>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $hiredCount }} kandidat sudah hired dari {{ $totalQuota }} kuota lowongan aktif.</p>
                    </div>
                </aside>
            </section>
        </div>
    </div>
</x-app-layout>
