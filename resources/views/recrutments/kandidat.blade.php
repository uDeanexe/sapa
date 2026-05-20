<x-app-layout>
    @php
        $candidateRows = $candidates->map(fn ($candidate) => [
            'id' => $candidate->id,
            'name' => $candidate->name,
            'position' => $candidate->position,
            'phone' => $candidate->phone,
            'source' => $candidate->source,
            'stage' => $candidate->stage,
            'score' => $candidate->score ?? 0,
            'notes' => $candidate->screening_notes,
            'cv_url' => $candidate->cv_url,
        ])->values();
        $averageScore = round($candidates->avg('score') ?? 0);
    @endphp

    <div
        class="admin-shell"
        x-data="{
            showCreate: @js($errors->any()),
            selected: null,
            search: '',
            stage: 'all',
            rows: @js($candidateRows),
            close() { this.showCreate = false },
            openCreate() {
                this.showCreate = true;
                this.$nextTick(() => this.$refs.nameInput?.focus());
            },
            matches(row) {
                const keyword = this.search.trim().toLowerCase();
                const text = [row.name, row.position, row.phone, row.source, row.stage].join(' ').toLowerCase();
                return (keyword === '' || text.includes(keyword)) && (this.stage === 'all' || row.stage === this.stage);
            }
        }"
    >
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Kandidat</h2>
                        <p class="admin-subtitle">Kelola data kandidat, tahapan seleksi, nilai screening, dan sumber lamaran.</p>
                    </div>
                    <button type="button" class="btn-primary-soft" @click="openCreate()">
                        <i class="fas fa-user-plus mr-2"></i>
                        Tambah Kandidat
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="metric-card"><p class="text-sm font-semibold text-slate-500">Total Kandidat</p><p class="mt-3 text-3xl font-bold text-slate-950">{{ $candidates->count() }}</p></div>
                <div class="metric-card metric-sky"><p class="text-sm font-semibold text-slate-500">Interview</p><p class="mt-3 text-3xl font-bold text-sky-700">{{ $candidates->where('stage', 'Interview')->count() }}</p></div>
                <div class="metric-card metric-emerald"><p class="text-sm font-semibold text-slate-500">Offering</p><p class="mt-3 text-3xl font-bold text-emerald-700">{{ $candidates->where('stage', 'Offering')->count() }}</p></div>
                <div class="metric-card metric-indigo"><p class="text-sm font-semibold text-slate-500">Rata-rata Skor</p><p class="mt-3 text-3xl font-bold text-slate-950">{{ $averageScore }}</p></div>
            </section>

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_360px]">
                <div class="app-surface">
                    <div class="app-toolbar">
                        <div>
                            <h3 class="app-section-title">Daftar Kandidat</h3>
                            <p class="app-section-subtitle">Klik baris untuk melihat ringkasan kandidat.</p>
                        </div>
                        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input type="text" x-model="search" class="form-control w-full pl-8 sm:w-64" placeholder="Cari kandidat...">
                            </div>
                            <select x-model="stage" class="form-control w-full sm:w-40">
                                <option value="all">Semua tahap</option>
                                @foreach(['Applied', 'Screening', 'Interview', 'Offering', 'Hired', 'Rejected'] as $stage)
                                    <option value="{{ $stage }}">{{ $stage }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="app-table-wrap rounded-none border-0 shadow-none">
                        <table class="data-table admin-table-fixed">
                            <colgroup>
                                <col class="w-[22%]">
                                <col class="w-[18%]">
                                <col class="w-[14%]">
                                <col class="w-[12%]">
                                <col class="w-[12%]">
                                <col class="w-[8%]">
                                <col class="w-[14%]">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Kandidat</th>
                                    <th>Posisi</th>
                                    <th>Kontak</th>
                                    <th>Sumber</th>
                                    <th>Tahap</th>
                                    <th>Skor</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in rows" :key="row.id">
                                    <tr x-show="matches(row)" class="data-table-row cursor-pointer" @click="selected = row">
                                        <td><p class="font-semibold text-slate-950" x-text="row.name"></p><p class="mt-1 truncate text-xs text-slate-500" x-text="row.notes || 'Belum ada catatan screening.'"></p></td>
                                        <td class="text-slate-600" x-text="row.position"></td>
                                        <td class="text-slate-600" x-text="row.phone"></td>
                                        <td><span class="app-badge-muted" x-text="row.source"></span></td>
                                        <td>
                                            <span class="app-badge bg-sky-100 text-sky-700" x-show="row.stage === 'Interview'" x-text="row.stage"></span>
                                            <span class="app-badge-success" x-show="['Offering', 'Hired'].includes(row.stage)" x-text="row.stage"></span>
                                            <span class="app-badge-warning" x-show="row.stage === 'Screening'" x-text="row.stage"></span>
                                            <span class="app-badge bg-rose-100 text-rose-700" x-show="row.stage === 'Rejected'" x-text="row.stage"></span>
                                            <span class="app-badge-muted" x-show="row.stage === 'Applied'" x-text="row.stage"></span>
                                        </td>
                                        <td><span class="font-bold text-slate-950" x-text="row.score"></span></td>
                                        <td>
                                            <div class="admin-table-actions">
                                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" @click.stop="selected = row" title="Detail"><i class="fas fa-eye text-xs"></i></button>
                                                <a x-show="row.cv_url" :href="row.cv_url" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" title="CV"><i class="fas fa-file-lines text-xs"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="rows.filter((row) => matches(row)).length === 0" x-cloak>
                                    <td colspan="7">
                                        <div class="app-empty-state">
                                            <div class="app-empty-state-icon"><i class="fas fa-user-group"></i></div>
                                            <p class="mt-3 font-semibold text-slate-900">Tidak ada kandidat yang cocok</p>
                                            <p class="mt-1 text-sm text-slate-500">Silakan ubah pencarian atau filter tahap.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="app-surface p-5">
                        <h3 class="app-section-title">Detail Kandidat</h3>
                        <div x-show="!selected" class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm leading-6 text-slate-500">Pilih kandidat dari tabel untuk melihat ringkasan dan aksi lanjutan.</p>
                        </div>
                        <div x-show="selected" x-cloak class="mt-4 space-y-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama</p>
                                <p class="mt-1 font-bold text-slate-950" x-text="selected?.name"></p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-slate-200 p-4"><p class="text-xs text-slate-500">Tahap</p><p class="mt-1 font-bold text-slate-950" x-text="selected?.stage"></p></div>
                                <div class="rounded-xl border border-slate-200 p-4"><p class="text-xs text-slate-500">Skor</p><p class="mt-1 text-2xl font-bold text-emerald-700" x-text="selected?.score"></p></div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Catatan</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600" x-text="selected?.notes || 'Belum ada catatan.'"></p>
                            </div>
                            <div class="flex gap-2">
                                <a x-show="selected?.cv_url" :href="selected?.cv_url" target="_blank" class="btn-secondary-soft flex-1"><i class="fas fa-file-lines mr-2"></i>CV</a>
                                <form method="POST" class="flex-1" :action="selected ? `/recruitment/kandidat/${selected.id}` : '#'" data-confirm="Hapus kandidat terpilih?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700" :disabled="!selected">
                                        <i class="fas fa-trash mr-2"></i>Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>
        </div>

        <div x-show="showCreate" x-cloak x-transition.opacity class="modal-backdrop" @keydown.escape.window="close()" @click.self="close()">
            <div class="modal-panel max-w-3xl">
                <div class="modal-header">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="modal-title">Tambah Kandidat</h3>
                            <p class="modal-subtitle">Masukkan data kandidat baru dan posisi yang dilamar.</p>
                        </div>
                        <button type="button" class="modal-close" @click="close()" aria-label="Tutup popup"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <form method="POST" action="{{ route('recruitment.candidates.store') }}" enctype="multipart/form-data" class="modal-body space-y-4" data-submit-lock>
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label><span class="field-label">Nama Kandidat</span><input name="name" value="{{ old('name') }}" x-ref="nameInput" class="form-control" placeholder="Nama lengkap" required></label>
                        <label><span class="field-label">No. Telepon</span><input name="phone" value="{{ old('phone') }}" class="form-control" placeholder="0812..." required></label>
                        <label>
                            <span class="field-label">Lowongan Terkait</span>
                            <select name="recruitment_opening_id" class="form-control">
                                <option value="">Tidak ditautkan</option>
                                @foreach($openings as $opening)
                                    <option value="{{ $opening->id }}" @selected((string) old('recruitment_opening_id') === (string) $opening->id)>{{ $opening->title }} - {{ $opening->division }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label><span class="field-label">Posisi Dilamar</span><input name="position" value="{{ old('position') }}" class="form-control" placeholder="Teknisi Lapangan" required></label>
                        <label><span class="field-label">Sumber</span><select name="source" class="form-control" required>@foreach(['Job Portal', 'Referral', 'LinkedIn', 'Walk-in'] as $source)<option @selected(old('source', 'Job Portal') === $source)>{{ $source }}</option>@endforeach</select></label>
                        <label><span class="field-label">Tahap</span><select name="stage" class="form-control" required>@foreach(['Applied', 'Screening', 'Interview', 'Offering', 'Hired', 'Rejected'] as $stage)<option @selected(old('stage', 'Applied') === $stage)>{{ $stage }}</option>@endforeach</select></label>
                        <label><span class="field-label">Skor</span><input name="score" value="{{ old('score', 0) }}" type="number" min="0" max="100" class="form-control" placeholder="80"></label>
                        <label><span class="field-label">Upload CV</span><input name="cv" type="file" class="form-control" accept=".pdf,.doc,.docx"></label>
                    </div>
                    <label><span class="field-label">Catatan Screening</span><textarea name="screening_notes" class="form-control" rows="4" placeholder="Pengalaman, ekspektasi gaji, availability, dan catatan awal...">{{ old('screening_notes') }}</textarea></label>
                    <div class="modal-footer -mx-6 -mb-6 mt-6">
                        <button type="button" class="btn-secondary-soft" @click="close()">Batal</button>
                        <button type="submit" class="btn-primary-soft">Simpan Kandidat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
