<x-app-layout>
    @php
        $summary = [
            'total' => $jobs->count(),
            'pending' => $jobs->where('status', 'pending')->count(),
            'process' => $jobs->where('status', 'process')->count(),
            'completed' => $jobs->where('status', 'completed')->count(),
            'overdue' => $jobs->filter->is_overdue->count(),
        ];

        $statusLabels = [
            'pending' => 'Pending',
            'process' => 'Dalam Proses',
            'completed' => 'Selesai',
        ];
    @endphp

    <div
        class="admin-shell"
        x-data="{
            search: '',
            status: 'all',
            matches(text, jobStatus) {
                const keyword = this.search.trim().toLowerCase();
                const textMatch = keyword === '' || text.toLowerCase().includes(keyword);
                const statusMatch = this.status === 'all' || this.status === jobStatus;

                return textMatch && statusMatch;
            }
        }"
    >
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Riwayat Tugas</h2>
                        <p class="admin-subtitle">Pantau posisi step, bukti pekerjaan, feedback pimpinan, dan komentar dalam satu halaman.</p>
                    </div>

                    @if(in_array(Auth::user()->role, ['kepala', 'admin', 'karyawan'], true))
                        <a href="{{ route('jobs.create') }}" class="btn-primary-soft">
                            <i class="fas fa-plus mr-2"></i>
                            Buat Tugas
                        </a>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div id="job-history-success" class="alert-success flex items-center justify-between gap-4">
                    <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                    <button type="button" data-dismiss="#job-history-success" class="text-emerald-700 hover:text-emerald-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div id="job-history-error" class="alert-error flex items-center justify-between gap-4">
                    <span><i class="fas fa-circle-exclamation mr-2"></i>{{ session('error') }}</span>
                    <button type="button" data-dismiss="#job-history-error" class="text-rose-700 hover:text-rose-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="metric-card">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-500">Total Tugas</p>
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <i class="fas fa-clipboard-list"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ $summary['total'] }}</p>
                </div>

                <div class="metric-card metric-amber">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-500">Pending</p>
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <i class="fas fa-clock"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ $summary['pending'] }}</p>
                </div>

                <div class="metric-card metric-sky">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-500">Proses</p>
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                            <i class="fas fa-spinner"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ $summary['process'] }}</p>
                </div>

                <div class="metric-card metric-emerald">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-500">Selesai</p>
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <i class="fas fa-check"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ $summary['completed'] }}</p>
                </div>

                <div class="metric-card metric-rose">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-500">Overdue</p>
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                            <i class="fas fa-triangle-exclamation"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ $summary['overdue'] }}</p>
                </div>
            </section>

            <section class="app-surface">
                <div class="app-toolbar">
                    <div>
                        <h3 class="app-section-title">Daftar Riwayat</h3>
                        <p class="app-section-subtitle">Step aktif ditampilkan besar di sisi kanan setiap tugas.</p>
                    </div>

                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input type="text" x-model="search" placeholder="Cari tugas, client, teknisi..." class="form-control w-full pl-8 sm:w-72">
                        </div>
                        <select x-model="status" class="form-control w-full sm:w-44">
                            <option value="all">Semua status</option>
                            <option value="pending">Pending</option>
                            <option value="process">Dalam proses</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($jobs as $job)
                        @php
                            $trackerSteps = $job->trackers->pluck('step_number')->unique();
                            $completedSteps = min(4, $trackerSteps->count());
                            $activeStep = match ($job->status) {
                                'pending' => 0,
                                'completed' => 4,
                                default => max(1, min(4, (int) $job->current_step)),
                            };
                            $progressPercent = $job->status === 'completed' ? 100 : min(100, ($completedSteps / 4) * 100);
                            $stepHeadline = match ($job->status) {
                                'pending' => 'Belum mulai',
                                'completed' => 'Selesai',
                                default => 'Step '.$activeStep.' dari 4',
                            };
                            $stepCaption = match ($job->status) {
                                'pending' => 'Menunggu teknisi mengambil tugas',
                                'completed' => 'Semua step pekerjaan sudah selesai',
                                default => $completedSteps.' step selesai, lanjut step '.$activeStep,
                            };
                            $statusClass = match ($job->status) {
                                'completed' => 'app-badge-success',
                                'process' => 'app-badge bg-sky-100 text-sky-700',
                                default => 'app-badge-warning',
                            };
                            $searchText = implode(' ', [
                                $job->title,
                                $job->client_name,
                                $job->whatsapp_number,
                                $job->location,
                                $job->google_maps_link,
                                $job->description,
                                $job->status,
                                $job->cs->name ?? '',
                                $job->technician->name ?? '',
                                $job->technician->division->name ?? '',
                            ]);
                        @endphp

                        <article
                            x-show="matches(@js($searchText), @js($job->status))"
                            class="bg-white"
                        >
                            <details class="group">
                                <summary class="list-none cursor-pointer px-5 py-5 transition hover:bg-slate-50">
                                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_340px_28px] xl:items-center">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="{{ $statusClass }}">{{ $statusLabels[$job->status] ?? ucfirst($job->status) }}</span>
                                                @if($job->is_overdue)
                                                    <span class="app-badge bg-rose-100 text-rose-700">Overdue</span>
                                                @endif
                                                <span class="app-badge-muted">{{ $completedSteps }}/4 step terisi</span>
                                            </div>

                                            <h3 class="mt-3 text-lg font-bold text-slate-950">{{ $job->title }}</h3>
                                            <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">{{ $job->description ?: 'Tidak ada deskripsi tugas.' }}</p>

                                            <div class="mt-4 grid grid-cols-1 gap-3 text-xs text-slate-500 sm:grid-cols-2 xl:grid-cols-4">
                                                <div>
                                                    <span class="block font-semibold text-slate-700">Client</span>
                                                    {{ $job->client_name ?: '-' }}
                                                </div>
                                                <div>
                                                    <span class="block font-semibold text-slate-700">Teknisi</span>
                                                    {{ $job->technician->name ?? '-' }}
                                                </div>
                                                <div>
                                                    <span class="block font-semibold text-slate-700">Divisi</span>
                                                    {{ $job->technician->division->name ?? '-' }}
                                                </div>
                                                <div>
                                                    <span class="block font-semibold text-slate-700">Dibuat</span>
                                                    {{ $job->created_at ? $job->created_at->format('d M Y H:i') : '-' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Posisi Tugas</p>
                                                    <p class="mt-1 text-2xl font-bold text-slate-950">{{ $stepHeadline }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $stepCaption }}</p>
                                                </div>
                                                <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $job->status === 'completed' ? 'bg-emerald-600' : ($job->status === 'process' ? 'bg-sky-600' : 'bg-amber-500') }} text-lg font-bold text-white">
                                                    {{ $activeStep ?: '-' }}
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <div class="mb-2 flex items-center justify-between text-xs font-semibold text-slate-500">
                                                    <span>Progress</span>
                                                    <span>{{ $progressPercent }}%</span>
                                                </div>
                                                <div class="h-2.5 overflow-hidden rounded-full bg-white">
                                                    <div class="h-full rounded-full bg-emerald-600" data-progress-width="{{ $progressPercent }}"></div>
                                                </div>
                                            </div>

                                            <div class="mt-3 grid grid-cols-4 gap-2">
                                                @for($step = 1; $step <= 4; $step++)
                                                    @php
                                                        $isDone = $trackerSteps->contains($step) || $job->status === 'completed';
                                                        $isCurrent = $job->status === 'process' && $activeStep === $step;
                                                    @endphp
                                                    <div class="rounded-lg border py-2 text-center text-xs font-bold {{ $isDone ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : ($isCurrent ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-slate-200 bg-white text-slate-400') }}">
                                                        {{ $step }}
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>

                                        <i class="fas fa-chevron-down hidden text-slate-400 transition group-open:rotate-180 xl:block"></i>
                                    </div>
                                </summary>

                                <div class="border-t border-slate-100 bg-slate-50/70 p-5">
                                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_360px]">
                                        <div class="space-y-5">
                                            <div class="rounded-xl border border-slate-200 bg-white p-5">
                                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                    <div>
                                                        <h4 class="text-sm font-semibold text-slate-950">Detail Step Tracker</h4>
                                                        <p class="mt-1 text-xs text-slate-500">Urutan laporan pekerjaan dari step 1 sampai step 4.</p>
                                                    </div>
                                                    <span class="app-badge-muted">{{ $completedSteps }} laporan</span>
                                                </div>

                                                <div class="mt-5 space-y-4">
                                                    @for($step = 1; $step <= 4; $step++)
                                                        @php
                                                            $tracker = $job->trackers->firstWhere('step_number', $step);
                                                            $isCurrent = $job->status === 'process' && $activeStep === $step;
                                                        @endphp

                                                        <div class="grid grid-cols-[44px_1fr] gap-4">
                                                            <div class="flex flex-col items-center">
                                                                <div class="flex h-11 w-11 items-center justify-center rounded-xl border text-sm font-bold {{ $tracker ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : ($isCurrent ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-slate-200 bg-slate-50 text-slate-400') }}">
                                                                    {{ $step }}
                                                                </div>
                                                                @if($step < 4)
                                                                    <div class="mt-2 h-full min-h-8 w-px bg-slate-200"></div>
                                                                @endif
                                                            </div>

                                                            <div class="rounded-xl border {{ $tracker ? 'border-emerald-200 bg-emerald-50/40' : ($isCurrent ? 'border-sky-200 bg-sky-50/60' : 'border-slate-200 bg-white') }} p-4">
                                                                <div class="flex flex-wrap items-start justify-between gap-3">
                                                                    <div>
                                                                        <p class="text-sm font-semibold text-slate-950">Step {{ $step }}</p>
                                                                        <p class="mt-1 text-xs text-slate-500">
                                                                            @if($tracker)
                                                                                Diisi {{ $tracker->created_at ? $tracker->created_at->format('d M Y H:i') : '-' }}
                                                                            @elseif($isCurrent)
                                                                                Sedang dikerjakan sekarang
                                                                            @else
                                                                                Belum ada laporan
                                                                            @endif
                                                                        </p>
                                                                    </div>
                                                                    <span class="{{ $tracker ? 'app-badge-success' : ($isCurrent ? 'app-badge bg-sky-100 text-sky-700' : 'app-badge-muted') }}">
                                                                        {{ $tracker ? 'Selesai' : ($isCurrent ? 'Aktif' : 'Menunggu') }}
                                                                    </span>
                                                                </div>

                                                                @if($tracker)
                                                                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $tracker->description_value ?: 'Tidak ada deskripsi step.' }}</p>
                                                                    <div class="mt-4 flex flex-wrap gap-3">
                                                                        @if($tracker->photo_path)
                                                                            <x-lightbox-image 
                                                                                src="{{ $tracker->public_photo_url }}"
                                                                                alt="Foto step {{ $step }}"
                                                                                class="h-24 w-32 rounded-lg border border-slate-200 object-cover shadow-sm"
                                                                            />
                                                                        @endif

                                                                        @if($tracker->video_path)
                                                                            <x-lightbox-video 
                                                                                src="{{ $tracker->public_video_url }}"
                                                                                alt="Video step {{ $step }}"
                                                                                class="h-24 w-40 rounded-lg bg-black"
                                                                            />
                                                                        @endif
                                                                    </div>
                                                                @else
                                                                    <p class="mt-3 text-sm leading-6 text-slate-500">Belum ada bukti atau catatan untuk step ini.</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>

                                            @if(Auth::user()->role === 'kepala')
                                                <form action="{{ route('jobs.feedback', $job->id) }}" method="POST" class="rounded-xl border border-slate-200 bg-white p-5" data-submit-lock>
                                                    @csrf
                                                    <label class="block">
                                                        <span class="field-label">Feedback / Instruksi Pimpinan</span>
                                                        <textarea name="feedback" rows="3" class="form-control" placeholder="Tulis feedback atau instruksi untuk pekerjaan ini...">{{ old('feedback', $job->feedback) }}</textarea>
                                                    </label>
                                                    <div class="mt-3 flex justify-end">
                                                        <x-primary-button class="btn-primary-soft normal-case tracking-normal">Simpan Feedback</x-primary-button>
                                                    </div>
                                                </form>
                                            @endif

                                            @if($job->feedback)
                                                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                                                    <p class="text-sm font-semibold text-emerald-950">Feedback Pimpinan</p>
                                                    <p class="mt-2 text-sm leading-6 text-emerald-800">{{ $job->feedback }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <aside class="space-y-4">
                                            <div class="rounded-xl border border-slate-200 bg-white p-5">
                                                <h4 class="text-sm font-semibold text-slate-950">Informasi Waktu</h4>
                                                <div class="mt-4 space-y-3 text-sm text-slate-600">
                                                    <div class="flex justify-between gap-4">
                                                        <span>Mulai</span>
                                                        <span class="text-right font-medium text-slate-900">{{ $job->start_time ? $job->start_time->format('d M Y H:i') : '-' }}</span>
                                                    </div>
                                                    <div class="flex justify-between gap-4">
                                                        <span>Deadline</span>
                                                        <span class="text-right font-medium text-slate-900">{{ $job->end_time ? $job->end_time->format('d M Y H:i') : '-' }}</span>
                                                    </div>
                                                    <div class="flex justify-between gap-4">
                                                        <span>Diambil</span>
                                                        <span class="text-right font-medium text-slate-900">{{ $job->accepted_at ? $job->accepted_at->format('d M Y H:i') : '-' }}</span>
                                                    </div>
                                                    <div class="flex justify-between gap-4">
                                                        <span>Selesai</span>
                                                        <span class="text-right font-medium text-slate-900">{{ $job->completed_at ? $job->completed_at->format('d M Y H:i') : '-' }}</span>
                                                    </div>
                                                    <div class="flex justify-between gap-4">
                                                        <span>Durasi Aktual</span>
                                                        <span class="text-right font-medium text-slate-900">{{ $job->actual_duration_label ?: '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="rounded-xl border border-slate-200 bg-white p-5">
                                                <h4 class="text-sm font-semibold text-slate-950">Penanggung Jawab</h4>
                                                <div class="mt-4 space-y-3 text-sm text-slate-600">
                                                    <div>
                                                        <span class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Customer Service</span>
                                                        <span class="font-medium text-slate-900">{{ $job->cs->name ?? '-' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="block text-xs font-semibold uppercase tracking-wide text-slate-400">Teknisi</span>
                                                        <span class="font-medium text-slate-900">{{ $job->technician->name ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($job->whatsapp_url)
                                                <div class="rounded-xl border border-slate-200 bg-white p-5">
                                                    <h4 class="text-sm font-semibold text-slate-950">Kontak Client</h4>
                                                    <a href="{{ $job->whatsapp_url }}" target="_blank" class="mt-3 inline-flex items-center text-sm font-semibold text-emerald-700 hover:text-emerald-900">
                                                        <i class="fab fa-whatsapp mr-2"></i>
                                                        {{ $job->whatsapp_number }}
                                                    </a>
                                                </div>
                                            @endif

                                            @if($job->location || $job->maps_url)
                                                <div class="rounded-xl border border-slate-200 bg-white p-5">
                                                    <h4 class="text-sm font-semibold text-slate-950">Lokasi</h4>
                                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $job->location ?: 'Alamat belum diisi.' }}</p>
                                                    @if($job->maps_url)
                                                        <a href="{{ $job->maps_url }}" target="_blank" class="mt-3 inline-flex items-center text-sm font-semibold text-emerald-700 hover:text-emerald-900">
                                                            <i class="fas fa-map-location-dot mr-2"></i>
                                                            Buka Maps
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="rounded-xl border border-slate-200 bg-white p-5">
                                                <div class="mb-4 flex items-center justify-between gap-3">
                                                    <h4 class="text-sm font-semibold text-slate-950">Komentar</h4>
                                                    <span class="app-badge-muted">{{ $job->comments->count() }}</span>
                                                </div>

                                                <div class="max-h-72 space-y-3 overflow-y-auto pr-1">
                                                    @forelse($job->comments as $comment)
                                                        @php
                                                            $initials = collect(explode(' ', $comment->user->name ?? '-'))
                                                                ->filter()
                                                                ->take(2)
                                                                ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                                                                ->join('');
                                                        @endphp

                                                        <div class="flex gap-3">
                                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-xs font-bold text-white">
                                                                {{ $initials ?: '?' }}
                                                            </div>
                                                            <div class="min-w-0 flex-1 rounded-lg bg-slate-50 px-3 py-2">
                                                                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                                                    <span class="font-semibold text-slate-800">{{ $comment->user->name ?? '-' }}</span>
                                                                    <span>{{ $comment->created_at ? $comment->created_at->diffForHumans() : '-' }}</span>

                                                                    @if(Auth::user()->role === 'kepala' || $comment->user_id === Auth::id())
                                                                        <form action="{{ route('jobs.comment.destroy', $comment->id) }}" method="POST" data-confirm="Hapus komentar ini?" data-submit-lock>
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="font-semibold text-rose-600 hover:text-rose-800">Hapus</button>
                                                                        </form>
                                                                    @endif
                                                                </div>
                                                                <p class="mt-1 text-sm leading-6 text-slate-600">{{ $comment->comment }}</p>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="rounded-lg bg-slate-50 px-3 py-4 text-center text-sm text-slate-500">Belum ada komentar.</p>
                                                    @endforelse
                                                </div>

                                                <form action="{{ route('jobs.comment', $job->id) }}" method="POST" class="mt-4 space-y-3" data-submit-lock>
                                                    @csrf
                                                    <textarea name="comment" rows="3" class="form-control" placeholder="Tulis komentar atau catatan pekerjaan..." required></textarea>
                                                    <div class="flex justify-end">
                                                        <x-primary-button class="btn-primary-soft normal-case tracking-normal">
                                                            <i class="fas fa-paper-plane mr-2"></i>
                                                            Kirim
                                                        </x-primary-button>
                                                    </div>
                                                </form>
                                            </div>
                                        </aside>
                                    </div>
                                </div>
                            </details>
                        </article>
                    @empty
                        <div class="px-5 py-14 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                <i class="fas fa-clipboard-list text-xl"></i>
                            </div>
                            <p class="mt-3 font-semibold text-slate-900">Belum ada riwayat tugas</p>
                            <p class="mt-1 text-sm text-slate-500">Tugas yang dibuat atau dikerjakan akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
