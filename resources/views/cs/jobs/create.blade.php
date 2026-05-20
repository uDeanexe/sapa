<x-app-layout>
    <div
        class="admin-shell"
        x-data="{
            createOpen: @js($errors->any()),
            cacheKey: 'jobs.create.draft',
            hasServerOld: @js(session()->hasOldInput()),
            form: {
                title: @js(old('title', '')),
                client_name: @js(old('client_name', '')),
                whatsapp_number: @js(old('whatsapp_number', '')),
                technician_id: @js(old('technician_id', '')),
                start_time: @js(old('start_time', '')),
                end_time: @js(old('end_time', '')),
                location: @js(old('location', '')),
                google_maps_link: @js(old('google_maps_link', '')),
                description: @js(old('description', '')),
            },
            init() {
                if (!this.hasServerOld) {
                    const draft = localStorage.getItem(this.cacheKey);
                    if (draft) {
                        try {
                            this.form = { ...this.form, ...JSON.parse(draft) };
                        } catch (error) {
                            localStorage.removeItem(this.cacheKey);
                        }
                    }
                }

                this.$watch('form', value => {
                    localStorage.setItem(this.cacheKey, JSON.stringify(value));
                }, { deep: true });
            },
            clearDraft() {
                localStorage.removeItem(this.cacheKey);
            }
        }"
        @keydown.escape.window="createOpen = false"
    >
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Buat Tugas Baru</h2>
                        <p class="admin-subtitle">Buat job order untuk teknisi dan pantau daftar tugas yang sudah dikirim.</p>
                    </div>

                    <button type="button" @click="createOpen = true" class="btn-primary-soft">
                        <i class="fas fa-plus mr-2"></i> Create Tugas
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert-success">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    <p class="font-semibold">Tugas belum bisa disimpan.</p>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div x-show="createOpen" x-cloak x-transition.opacity class="modal-backdrop">
                <div
                    @click.outside="createOpen = false"
                    x-transition:enter="transform transition ease-out duration-200"
                    x-transition:enter-start="scale-95 opacity-0"
                    x-transition:enter-end="scale-100 opacity-100"
                    x-transition:leave="transform transition ease-in duration-150"
                    x-transition:leave-start="scale-100 opacity-100"
                    x-transition:leave-end="scale-95 opacity-0"
                    class="modal-panel"
                >
                    <div class="modal-header">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="modal-title">Create Tugas</h3>
                                <p class="modal-subtitle">Lengkapi informasi pekerjaan sebelum dikirim ke teknisi.</p>
                            </div>
                            <button type="button" @click="createOpen = false" class="modal-close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="mt-4 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
                            Isian disimpan sementara sampai tugas berhasil dikirim.
                        </div>
                    </div>

                    <form action="{{ route('jobs.store') }}" method="POST" class="modal-form" @submit="clearDraft()">
                        @csrf

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <label class="md:col-span-2">
                                <span class="field-label">Judul Tugas</span>
                                <x-text-input name="title" placeholder="Contoh: Maintenance GPS Tracker" x-model="form.title" class="form-control" required />
                            </label>

                            <label>
                                <span class="field-label">Client</span>
                                <x-text-input name="client_name" placeholder="Nama client / perusahaan" x-model="form.client_name" class="form-control" />
                            </label>

                            <label>
                                <span class="field-label">No. WhatsApp</span>
                                <x-text-input name="whatsapp_number" placeholder="Contoh: 081234567890" x-model="form.whatsapp_number" class="form-control" />
                            </label>

                            <label>
                                <span class="field-label">Teknisi</span>
                                <select name="technician_id" x-model="form.technician_id" class="form-control" required>
                                    <option value="">Pilih teknisi</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->division->name ?? 'Tanpa Divisi' }})</option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span class="field-label">Mulai</span>
                                <input type="datetime-local" name="start_time" x-model="form.start_time" class="form-control">
                            </label>

                            <label>
                                <span class="field-label">Deadline</span>
                                <input type="datetime-local" name="end_time" x-model="form.end_time" class="form-control">
                            </label>

                            <label class="md:col-span-2">
                                <span class="field-label">Lokasi</span>
                                <textarea name="location" rows="3" placeholder="Alamat/lokasi pekerjaan" x-model="form.location" class="form-control"></textarea>
                            </label>

                            <label class="md:col-span-2">
                                <span class="field-label">Link Google Maps</span>
                                <x-text-input type="url" name="google_maps_link" placeholder="https://maps.app.goo.gl/..." x-model="form.google_maps_link" class="form-control" />
                            </label>

                            <label class="md:col-span-2">
                                <span class="field-label">Detail Tugas</span>
                                <textarea name="description" rows="4" placeholder="Detail instruksi pekerjaan..." x-model="form.description" class="form-control"></textarea>
                            </label>
                        </div>

                        <div class="modal-footer">
                            <button type="button" @click="createOpen = false" class="btn-secondary-soft">Batal</button>
                            <x-primary-button class="btn-primary-soft normal-case tracking-normal">
                                <i class="fas fa-paper-plane mr-2"></i> Kirim Tugas
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header flex items-center justify-between gap-3">
                    <div>
                        <h3 class="admin-card-title">Table Tugas</h3>
                        <p class="mt-1 text-xs text-slate-500">Menampilkan {{ $jobs->count() }} tugas.</p>
                    </div>
                </div>

                <div class="app-table-wrap rounded-none border-0 shadow-none">
                    <table class="data-table admin-table-fixed admin-table-xl">
                        <colgroup>
                            <col class="w-[6%]">
                            <col class="w-[19%]">
                            <col class="w-[20%]">
                            <col class="w-[16%]">
                            <col class="w-[16%]">
                            <col class="w-[10%]">
                            <col class="w-[13%]">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tugas</th>
                                <th>Client & Lokasi</th>
                                <th>Teknisi</th>
                                <th>Estimasi</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jobs as $job)
                                <tr class="data-table-row">
                                    <td class="text-slate-500">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="font-semibold text-slate-950">{{ $job->title }}</div>
                                        <div class="mt-1 text-xs leading-5 text-slate-500">Dibuat oleh {{ $job->cs->name ?? '-' }}</div>
                                        <div class="mt-2 admin-table-text">{{ $job->description ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="font-medium text-slate-800">{{ $job->client_name ?: '-' }}</div>
                                        @if($job->whatsapp_number)
                                            <a href="{{ $job->whatsapp_url }}" target="_blank" class="mt-1 inline-flex items-center text-xs font-semibold text-emerald-700 hover:text-emerald-900">
                                                <i class="fab fa-whatsapp mr-1"></i>{{ $job->whatsapp_number }}
                                            </a>
                                        @endif
                                        <div class="admin-table-text mt-1 text-xs">{{ $job->location ?: '-' }}</div>
                                        @if($job->maps_url)
                                            <a href="{{ $job->maps_url }}" target="_blank" class="mt-1 inline-flex items-center text-xs font-semibold text-emerald-700 hover:text-emerald-900">
                                                <i class="fas fa-map-location-dot mr-1"></i>Buka Maps
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-medium text-slate-800">{{ $job->technician->name ?? '-' }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $job->technician->division->name ?? 'Tanpa Divisi' }}</div>
                                    </td>
                                    <td class="text-slate-700">
                                        <div>{{ $job->start_time ? $job->start_time->format('d M Y H:i') : '-' }}</div>
                                        <div class="mt-1 text-xs text-slate-500">s/d {{ $job->end_time ? $job->end_time->format('d M Y H:i') : '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="app-badge {{ $job->status === 'completed' ? 'app-badge-success' : ($job->status === 'process' ? 'bg-sky-100 text-sky-700' : 'app-badge-warning') }}">
                                            {{ $job->status }}
                                        </span>
                                        <div class="mt-1 text-xs text-slate-500">Step {{ $job->current_step }}</div>
                                    </td>
                                    <td class="text-slate-700">
                                        {{ $job->created_at ? $job->created_at->format('d M Y H:i') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="app-empty-state">
                                            <div class="app-empty-state-icon"><i class="fas fa-briefcase"></i></div>
                                            <p class="mt-3 font-semibold text-slate-900">Belum ada tugas</p>
                                            <p class="mt-1 text-sm text-slate-500">Klik Create Tugas untuk membuat data pertama.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
