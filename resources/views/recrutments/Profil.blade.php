<x-app-layout>
    @php
        $activeOpenings = $openings->where('status', 'Aktif')->count();
        $interviewCount = $candidates->where('stage', 'Interview')->count();
        $hiringCount = $candidates->where('stage', 'Hired')->count();
        $metrics = [
            ['label' => 'Lowongan Aktif', 'value' => $activeOpenings, 'icon' => 'fa-briefcase', 'tone' => 'emerald'],
            ['label' => 'Kandidat Masuk', 'value' => $candidates->count(), 'icon' => 'fa-users', 'tone' => 'sky'],
            ['label' => 'Interview', 'value' => $interviewCount, 'icon' => 'fa-calendar-check', 'tone' => 'amber'],
            ['label' => 'Hired', 'value' => $hiringCount, 'icon' => 'fa-user-check', 'tone' => 'slate'],
        ];
        $sources = collect(['Job Portal', 'Referral', 'LinkedIn', 'Walk-in'])->map(function ($source) use ($candidates) {
            $count = $candidates->where('source', $source)->count();
            return [
                'name' => $source,
                'count' => $count,
                'percent' => $candidates->count() > 0 ? round(($count / $candidates->count()) * 100) : 0,
            ];
        });
        $priorityOpenings = $openings->whereIn('status', ['Aktif', 'Review'])->sortByDesc(fn ($opening) => $opening->priority === 'Tinggi')->take(3);
    @endphp

    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Profil Rekrutmen</h2>
                        <p class="admin-subtitle">Ringkasan kesehatan proses rekrutmen, sumber kandidat, dan kebutuhan tim.</p>
                    </div>
                    <a href="{{ route('recruitment.lowongan') }}" class="btn-primary-soft">
                        <i class="fas fa-plus mr-2"></i>
                        Buka Lowongan
                    </a>
                </div>
            </div>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($metrics as $metric)
                    @php
                        $tone = [
                            'emerald' => 'bg-emerald-50 text-emerald-600',
                            'sky' => 'bg-sky-50 text-sky-600',
                            'amber' => 'bg-amber-50 text-amber-600',
                            'slate' => 'bg-slate-100 text-slate-600',
                        ][$metric['tone']];
                    @endphp
                    <div class="app-surface p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-500">{{ $metric['label'] }}</p>
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg {{ $tone }}">
                                <i class="fas {{ $metric['icon'] }}"></i>
                            </span>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-slate-950">{{ $metric['value'] }}</p>
                    </div>
                @endforeach
            </section>

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="app-surface p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="app-section-title">Alur Rekrutmen</h3>
                            <p class="app-section-subtitle">Tahapan standar dari kandidat masuk sampai offering.</p>
                        </div>
                        <span class="app-badge-success">SLA {{ $openings->first()?->sla ?? '14 hari' }}</span>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                        @foreach(['Screening CV', 'Interview HR', 'Interview User', 'Offering'] as $index => $step)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $index < 2 ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600' }} font-bold">
                                    {{ $index + 1 }}
                                </div>
                                <p class="mt-4 font-semibold text-slate-950">{{ $step }}</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ $index < 2 ? 'Tahap paling aktif minggu ini.' : 'Menunggu kandidat lolos tahap sebelumnya.' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="app-surface p-5">
                    <h3 class="app-section-title">Sumber Kandidat</h3>
                    <div class="mt-5 space-y-4">
                        @foreach($sources as $source)
                            <div>
                                <div class="mb-2 flex items-center justify-between text-sm">
                                    <span class="font-semibold text-slate-800">{{ $source['name'] }}</span>
                                    <span class="text-slate-500">{{ $source['count'] }}</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-emerald-600" data-progress-width="{{ $source['percent'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                <div class="app-surface p-5 xl:col-span-2">
                    <h3 class="app-section-title">Kebutuhan Prioritas</h3>
                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                        @forelse($priorityOpenings as $opening)
                            @php
                                $tone = $opening->priority === 'Tinggi' ? 'border-rose-200 bg-rose-50 text-rose-700' : ($opening->priority === 'Rendah' ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-amber-200 bg-amber-50 text-amber-700');
                            @endphp
                            <div class="rounded-xl border p-4 {{ $tone }}">
                                <p class="font-semibold text-slate-950">{{ $opening->title }}</p>
                                <p class="mt-1 text-sm">Butuh {{ $opening->quota }} orang, {{ strtolower($opening->priority ?: 'sedang') }}.</p>
                            </div>
                        @empty
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500 md:col-span-3">Belum ada lowongan aktif atau review.</div>
                        @endforelse
                    </div>
                </div>

                <div class="app-surface p-5">
                    <h3 class="app-section-title">Catatan HR</h3>
                    <p class="mt-4 text-sm leading-6 text-slate-600">Prioritaskan kandidat dengan pengalaman yang paling sesuai lowongan aktif. Jadwalkan interview user maksimal 2 hari setelah screening lolos.</p>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
