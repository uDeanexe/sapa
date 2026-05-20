<x-app-layout>
    @php
        $pendingCount = $jobs->where('status', 'pending')->count();
        $processCount = $jobs->where('status', 'process')->count();
        $overdueCount = $jobs->filter->is_overdue->count();
        $division = Auth::user()->division;
        $hasCheckedIn = $todayPresence !== null;
        $hasCheckedOut = $todayPresence?->check_out !== null;
    @endphp

    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Tracker Kerja Lapangan</h2>
                        <p class="admin-subtitle">Mulai dari absensi, ambil tugas, update progress, upload bukti, sampai pekerjaan selesai.</p>
                    </div>

                    <a href="{{ route('jobs.history') }}" class="btn-secondary-soft">
                        <i class="fas fa-history mr-2"></i>
                        Riwayat Tugas
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div id="technician-success" class="alert-success flex items-center justify-between gap-4">
                    <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                    <button type="button" data-dismiss="#technician-success" class="text-emerald-700 hover:text-emerald-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    <p class="font-semibold">Progress belum bisa disimpan.</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="metric-card {{ $hasCheckedIn ? 'metric-emerald' : 'metric-amber' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Absensi Hari Ini</p>
                            <p class="mt-3 text-2xl font-bold {{ $hasCheckedIn ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $hasCheckedIn ? 'Sudah Masuk' : 'Belum Masuk' }}
                            </p>
                        </div>
                        <span class="metric-icon {{ $hasCheckedIn ? 'text-emerald-600' : 'text-amber-600' }}">
                            <i class="fas fa-location-check"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-xs text-slate-500">
                        {{ $hasCheckedIn ? 'Masuk '.$todayPresence->check_in.($hasCheckedOut ? ' · Pulang '.$todayPresence->check_out : '') : 'Lakukan absensi dari aplikasi/mobile sebelum mulai kerja.' }}
                    </p>
                </div>

                <div class="metric-card metric-amber">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Tugas Baru</p>
                            <p class="mt-3 text-3xl font-bold text-amber-700">{{ $pendingCount }}</p>
                        </div>
                        <span class="metric-icon text-amber-500">
                            <i class="fas fa-tasks"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-xs text-slate-500">Menunggu diambil</p>
                </div>

                <div class="metric-card metric-sky">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Sedang Dikerjakan</p>
                            <p class="mt-3 text-3xl font-bold text-sky-700">{{ $processCount }}</p>
                        </div>
                        <span class="metric-icon text-sky-500">
                            <i class="fas fa-spinner"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-xs text-slate-500">Butuh update progress</p>
                </div>

                <div class="metric-card metric-rose">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Overdue</p>
                            <p class="mt-3 text-3xl font-bold text-rose-700">{{ $overdueCount }}</p>
                        </div>
                        <span class="metric-icon text-rose-500">
                            <i class="fas fa-exclamation-circle"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-xs text-slate-500">Lewat estimasi selesai</p>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 lg:grid-cols-12 mb-6">
                <div class="app-surface p-5 lg:col-span-7 xl:col-span-8">
                    <h3 class="app-section-title">Urutan Kerja</h3>
                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach([
                            ['label' => 'Absensi masuk', 'done' => $hasCheckedIn],
                            ['label' => 'Ambil tugas', 'done' => $processCount > 0],
                            ['label' => 'Update progress', 'done' => $jobs->sum(fn ($job) => $job->trackers->count()) > 0],
                            ['label' => 'Selesaikan pekerjaan', 'done' => false],
                        ] as $index => $item)
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $item['done'] ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }} text-sm font-bold shadow-sm">
                                        @if($item['done'])
                                            <i class="fas fa-check"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-950">{{ $item['label'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $item['done'] ? 'Selesai' : 'Tertunda' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="app-surface p-5 lg:col-span-5 xl:col-span-4">
                    <h3 class="app-section-title">Template Tim ({{ $division->name ?? '-' }})</h3>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        @for($i = 1; $i <= 4; $i++)
                            <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 shadow-sm">
                                <p class="text-xs font-semibold text-slate-900">Step {{ $i }}</p>
                                <p class="mt-1 text-xs font-medium text-slate-600 truncate" title="{{ $division?->{"step_$i"} ?: 'Belum diatur' }}">
                                    {{ $division?->{"step_$i"} ?: 'Belum diatur' }}
                                </p>
                                @php
                                    $reqs = collect([
                                        $division?->{"req_desc_$i"} ? 'Desc' : null,
                                        $division?->{"req_photo_$i"} ? 'Foto' : null,
                                        $division?->{"req_video_$i"} ? 'Video' : null,
                                    ])->filter()->implode(', ');
                                @endphp
                                <p class="mt-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $reqs ?: 'Opsional' }}</p>
                            </div>
                        @endfor
                    </div>
                </div>
            </section>

            <section class="space-y-6">
                    @forelse($jobs as $job)
                        @php
                            $nextStep = max(1, min((int) ($job->current_step ?: 1), 4));
                            $stepLabel = "step_{$nextStep}";
                            $stepName = $division?->{$stepLabel} ?: "Progress {$nextStep}";
                            $progressPercent = min(100, max(0, (($nextStep - 1) / 4) * 100));
                            $needsDescription = (bool) $division?->{"req_desc_$nextStep"};
                            $needsPhoto = (bool) $division?->{"req_photo_$nextStep"};
                            $needsVideo = (bool) $division?->{"req_video_$nextStep"};
                        @endphp

                        <article class="admin-card hover:shadow-xl transition-all duration-200">
                            <div class="admin-card-header flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-xl font-bold text-slate-950">{{ $job->title }}</h3>
                                        @if($job->status === 'pending')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200">Pending</span>
                                        @elseif($job->status === 'process')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-900 border border-blue-200"><span class="w-1.5 h-1.5 mr-1.5 bg-blue-600 rounded-full animate-pulse"></span>Process</span>
                                        @elseif($job->status === 'completed')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-200">Completed</span>
                                        @endif
                                        @if($job->is_overdue)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-900 border border-rose-200">Overdue</span>
                                        @endif
                                    </div>
                                    <p class="text-sm leading-6 text-slate-500">{{ $job->description ?: 'Tidak ada instruksi tambahan.' }}</p>
                                </div>

                                @if($job->status === 'pending')
                                    <form action="{{ route('jobs.accept', $job->id) }}" method="POST" data-confirm="Ambil tugas ini dan mulai progress?" data-submit-lock>
                                        @csrf
                                        <button type="submit" class="btn-primary-soft inline-flex items-center gap-2">
                                            <i class="fas fa-play"></i>
                                            Ambil Tugas
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 gap-8 p-6 lg:grid-cols-2">
                                <div class="space-y-6">
                                    <div class="grid grid-cols-2 gap-4 auto-rows-fr">
                                        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 shadow-sm h-full flex flex-col justify-between">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-indigo-700">Client</p>
                                                <p class="mt-1 text-lg font-extrabold text-slate-900 line-clamp-2">{{ $job->client_name ?: '-' }}</p>
                                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">WhatsApp</p>
                                                @if($job->whatsapp_url)
                                                    <a href="{{ $job->whatsapp_url }}" target="_blank" class="mt-1 inline-flex items-center font-semibold text-emerald-700 hover:text-emerald-900 break-all">
                                                        <i class="fab fa-whatsapp mr-2"></i>{{ $job->whatsapp_number }}
                                                    </a>
                                                @else
                                                    <p class="mt-1 font-semibold text-slate-900">-</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 h-full flex flex-col justify-between">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">WhatsApp</p>
                                                @if($job->whatsapp_url)
                                                    <a href="{{ $job->whatsapp_url }}" target="_blank" class="mt-1 inline-flex items-center font-semibold text-emerald-700 hover:text-emerald-900">
                                                        <i class="fab fa-whatsapp mr-2"></i>{{ $job->whatsapp_number }}
                                                    </a>
                                                @else
                                                    <p class="mt-1 font-semibold text-slate-900">-</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="{{ $job->is_overdue ? 'rounded-xl border border-rose-600 bg-rose-600 p-4 text-white shadow-lg h-full flex flex-col justify-between' : 'rounded-xl border border-rose-200 bg-rose-50 p-4 shadow-sm h-full flex flex-col justify-between' }}">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide {{ $job->is_overdue ? 'text-white/80' : 'text-rose-700' }}">Deadline</p>
                                                <p class="mt-1 text-lg font-bold flex items-center gap-1 {{ $job->is_overdue ? 'text-white' : 'text-rose-700' }}"><i class="fas fa-clock text-sm"></i> {{ $job->end_time ? $job->end_time->format('d M Y H:i') : '-' }}</p>
                                                @if($job->is_overdue)
                                                    <p class="mt-2 text-sm font-semibold uppercase tracking-wide text-white/80">Segera selesaikan</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 h-full flex flex-col justify-between">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Dari</p>
                                                <p class="mt-1 font-semibold text-slate-900">{{ $job->cs->name ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-slate-200 p-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="font-semibold text-slate-950">Lokasi Pekerjaan</p>
                                                <p class="mt-1 text-sm leading-6 text-slate-500">{{ $job->location ?: 'Lokasi belum diisi.' }}</p>
                                            </div>
                                            @if($job->maps_url)
                                                <a href="{{ $job->maps_url }}" target="_blank" class="btn-secondary-soft shrink-0">
                                                    <i class="fas fa-location-dot mr-2"></i>
                                                    Maps
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between text-xs font-semibold text-slate-500">
                                            <span>Progress pekerjaan</span>
                                            <span>Step {{ $nextStep }} dari 4</span>
                                        </div>
                                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-700" style="width: {{ $progressPercent }}%"></div>
                                        </div>
                                        <p class="mt-2 text-xs text-slate-500">{{ $progressPercent }}% selesai</p>
                                    </div>

                                    @if($job->trackers->isNotEmpty())
                                        <div class="rounded-xl border border-slate-200">
                                            <div class="border-b border-slate-200 px-4 py-3">
                                                <p class="text-sm font-semibold text-slate-900">Progress Tersimpan</p>
                                            </div>
                                            <div class="divide-y divide-slate-100">
                                                @foreach($job->trackers->sortBy('step_number') as $tracker)
                                                    <div class="p-4">
                                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                                            <p class="font-semibold text-slate-900">Step {{ $tracker->step_number }}</p>
                                                            <span class="text-xs text-slate-400">{{ $tracker->created_at?->format('d M Y H:i') }}</span>
                                                        </div>
                                                        <p class="mt-1 text-sm leading-6 text-slate-500">{{ $tracker->description_value ?: 'Tanpa deskripsi.' }}</p>
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            @if($tracker->public_photo_url)
                                                                <x-lightbox-image 
                                                                    src="{{ $tracker->public_photo_url }}"
                                                                    alt="Foto bukti"
                                                                    class="h-12 w-16 rounded border border-slate-200 object-cover"
                                                                />
                                                            @endif
                                                            @if($tracker->public_video_url)
                                                                <x-lightbox-video 
                                                                    src="{{ $tracker->public_video_url }}"
                                                                    alt="Video bukti"
                                                                    class="h-12 w-20 rounded bg-black"
                                                                />
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <aside class="flex flex-col justify-center rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                                    @if($job->status === 'pending')
                                        <div class="app-empty-state px-0 py-8">
                                            <div class="app-empty-state-icon text-4xl text-slate-300"><i class="fas fa-hand-pointer"></i></div>
                                            <p class="mt-3 font-semibold text-slate-900">Ambil tugas terlebih dahulu</p>
                                            <p class="mt-1 text-sm text-slate-500 text-center">Form progress akan aktif setelah Anda mengambil tugas ini.</p>
                                        </div>
                                    @else
                                        <form action="{{ route('jobs.progress', $job->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5" data-submit-lock>
                                            @csrf

                                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-5">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Tahap Berikutnya</p>
                                                        <h4 class="mt-1.5 text-lg font-extrabold text-slate-900">{{ $stepName }}</h4>
                                                        <p class="mt-1 text-sm text-slate-600">Lengkapi bukti yang diminta untuk lanjut ke tahap selanjutnya.</p>
                                                    </div>
                                                    <div class="flex h-10 items-center justify-center rounded-full bg-emerald-100 px-4 text-sm font-bold text-emerald-700">Step {{ $nextStep }} / 4</div>
                                                </div>
                                            </div>

                                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                                <div class="border-b border-slate-100 pb-4 mb-4">
                                                    <h5 class="text-sm font-bold text-slate-900"><i class="fas fa-paperclip text-slate-400 mr-2"></i>Bukti Kerja</h5>
                                                    <p class="mt-1 text-xs text-slate-500">Upload foto atau video sesuai dengan persyaratan tahap ini.</p>
                                                </div>

                                                <div class="grid gap-4">
                                                    <div>
                                                        <label class="group relative flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-emerald-300 bg-emerald-50/30 p-6 text-center transition-all hover:bg-emerald-50">
                                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 transition-transform group-hover:scale-110">
                                                                <i class="fas fa-camera text-xl"></i>
                                                            </div>
                                                            <p class="mt-4 text-sm font-bold text-slate-900">Ketuk untuk pilih foto {!! $needsPhoto ? '<span class="text-rose-500">*</span>' : '<span class="font-normal text-slate-500">(Opsional)</span>' !!}</p>
                                                            <p class="mt-1 text-xs text-slate-500">Format PNG/JPG (Maks 5MB)</p>
                                                            <input type="file" name="photo" accept="image/*" class="hidden" {{ $needsPhoto ? 'required' : '' }} onchange="
                                                                const file = this.files[0];
                                                                if(file) {
                                                                    const reader = new FileReader();
                                                                    reader.onload = e => {
                                                                        const img = document.getElementById('preview-photo-{{ $job->id }}');
                                                                        img.src = e.target.result;
                                                                        img.classList.remove('hidden');
                                                                        document.getElementById('name-photo-{{ $job->id }}').innerText = file.name;
                                                                    };
                                                                    reader.readAsDataURL(file);
                                                                }
                                                            " />
                                                            <img id="preview-photo-{{ $job->id }}" src="#" class="hidden mx-auto mt-4 h-40 w-full max-w-[240px] rounded-xl border border-slate-200 object-cover shadow-sm" alt="Preview foto" />
                                                            <p id="name-photo-{{ $job->id }}" class="mt-2 text-xs font-semibold text-emerald-600"></p>
                                                        </label>
                                                    </div>

                                                    <div>
                                                        <label class="group relative flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-sky-300 bg-sky-50/30 p-6 text-center transition-all hover:bg-sky-50">
                                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sky-100 text-sky-600 transition-transform group-hover:scale-110">
                                                                <i class="fas fa-video text-xl"></i>
                                                            </div>
                                                            <p class="mt-4 text-sm font-bold text-slate-900">Ketuk untuk pilih video {!! $needsVideo ? '<span class="text-rose-500">*</span>' : '<span class="font-normal text-slate-500">(Opsional)</span>' !!}</p>
                                                            <p class="mt-1 text-xs text-slate-500">Format MP4/MOV (Maks 20MB)</p>
                                                            <input type="file" name="video" accept="video/*" class="hidden" {{ $needsVideo ? 'required' : '' }} onchange="
                                                                const file = this.files[0];
                                                                if(file) {
                                                                    const url = URL.createObjectURL(file);
                                                                    const vid = document.getElementById('preview-video-{{ $job->id }}');
                                                                    vid.src = url;
                                                                    vid.classList.remove('hidden');
                                                                    document.getElementById('name-video-{{ $job->id }}').innerText = file.name;
                                                                }
                                                            " />
                                                            <video id="preview-video-{{ $job->id }}" src="#" class="hidden mx-auto mt-4 h-40 w-full max-w-[240px] rounded-xl bg-black shadow-sm" controls muted></video>
                                                            <p id="name-video-{{ $job->id }}" class="mt-2 text-xs font-semibold text-sky-600"></p>
                                                        </label>
                                                    </div>
                                                </div>

                                                @php
                                                    $attachments = collect();
                                                    foreach ($job->trackers as $tracker) {
                                                        if ($tracker->public_photo_url) {
                                                            $attachments->push(['type' => 'photo', 'url' => $tracker->public_photo_url]);
                                                        }
                                                        if ($tracker->public_video_url) {
                                                            $attachments->push(['type' => 'video', 'url' => $tracker->public_video_url]);
                                                        }
                                                    }
                                                @endphp

                                                @if($attachments->isNotEmpty())
                                                    <div class="mt-6 pt-4 border-t border-slate-100">
                                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Bukti Tersimpan (Tahap Sebelumnya)</p>
                                                        <div class="mt-3 grid grid-cols-2 gap-3">
                                                            @foreach($attachments as $attachment)
                                                                @if($attachment['type'] === 'photo')
                                                                    <x-lightbox-image
                                                                        src="{{ $attachment['url'] }}"
                                                                        alt="Foto bukti"
                                                                        class="h-28 w-full rounded-xl border border-slate-200 object-cover shadow-sm"
                                                                    />
                                                                @else
                                                                    <x-lightbox-video
                                                                        src="{{ $attachment['url'] }}"
                                                                        alt="Video bukti"
                                                                        class="h-28 w-full rounded-xl bg-black shadow-sm"
                                                                    />
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                                @if($needsDescription)
                                                    <label>
                                                        <span class="mb-2 block text-sm font-bold text-slate-700">Deskripsi Pekerjaan <span class="text-rose-500">*</span></span>
                                                        <textarea name="description_value" rows="4" class="block w-full rounded-xl border-slate-300 p-3 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition-colors" placeholder="Jelaskan detail pekerjaan yang telah dilakukan pada tahap ini..." required></textarea>
                                                    </label>
                                                @else
                                                    <label>
                                                        <span class="mb-2 block text-sm font-bold text-slate-700">Catatan Tambahan <span class="text-slate-400 font-normal">(Opsional)</span></span>
                                                        <textarea name="description_value" rows="3" class="block w-full rounded-xl border-slate-300 p-3 text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring focus:ring-emerald-200 transition-colors" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                                                    </label>
                                                @endif
                                            </div>

                                            @if($nextStep === 4)
                                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                                                <label>
                                                    <span class="mb-2 block text-sm font-bold text-amber-900">Alasan Penyelesaian <span class="text-amber-700/70 font-normal">(Opsional)</span></span>
                                                    <textarea name="completion_reason" rows="2" class="block w-full rounded-xl border-amber-300 p-3 text-sm placeholder:text-amber-500/50 focus:border-amber-500 focus:ring focus:ring-amber-200 transition-colors bg-white" placeholder="Catatan akhir sebelum tugas dinyatakan selesai..."></textarea>
                                                </label>
                                            </div>
                                            @endif

                                            <button type="submit" class="btn-primary-soft w-full flex items-center justify-center gap-2 py-3.5 text-base font-bold transition-transform hover:scale-[1.02]">
                                                @if($nextStep === 4)
                                                    <i class="fas fa-check-circle text-lg"></i>
                                                    Konfirmasi Pekerjaan Selesai
                                                @else
                                                    Simpan & Lanjut ke Tahap {{ $nextStep + 1 }}
                                                    <i class="fas fa-arrow-right"></i>
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                </aside>
                            </div>
                        </article>
                    @empty
                        <div class="admin-card">
                            <div class="app-empty-state">
                                <div class="app-empty-state-icon"><i class="fas fa-clipboard-list"></i></div>
                                <p class="mt-3 font-semibold text-slate-900">Belum ada tugas untukmu</p>
                                <p class="mt-1 text-sm text-slate-500">Tugas baru dari admin lapangan akan muncul di halaman ini.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
