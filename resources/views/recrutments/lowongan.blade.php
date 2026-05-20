<x-app-layout>
    @php
        $activeCount = $openings->where('status', 'Aktif')->count();
        $reviewCount = $openings->where('status', 'Review')->count();
        $draftCount = $openings->where('status', 'Draft')->count();
        $totalApplicants = $openings->sum('candidates_count');
    @endphp

    <div
        class="admin-shell"
        x-data="{
            showCreate: @js($errors->any()),
            search: '',
            status: 'all',
            close() { this.showCreate = false },
            openCreate() {
                this.showCreate = true;
                this.$nextTick(() => this.$refs.titleInput?.focus());
            },
            matches(text, rowStatus) {
                const keyword = this.search.trim().toLowerCase();
                return (keyword === '' || text.toLowerCase().includes(keyword)) && (this.status === 'all' || this.status === rowStatus);
            }
        }"
    >
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Lowongan Pekerjaan</h2>
                        <p class="admin-subtitle">Kelola posisi yang dibuka, kuota kebutuhan, dan jumlah pelamar per lowongan.</p>
                    </div>
                    <button type="button" class="btn-primary-soft" @click="openCreate()">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Lowongan
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="metric-card metric-emerald">
                    <p class="text-sm font-semibold text-slate-500">Aktif</p>
                    <p class="mt-3 text-3xl font-bold text-emerald-700">{{ $activeCount }}</p>
                </div>
                <div class="metric-card metric-sky">
                    <p class="text-sm font-semibold text-slate-500">Review</p>
                    <p class="mt-3 text-3xl font-bold text-sky-700">{{ $reviewCount }}</p>
                </div>
                <div class="metric-card metric-amber">
                    <p class="text-sm font-semibold text-slate-500">Draft</p>
                    <p class="mt-3 text-3xl font-bold text-amber-700">{{ $draftCount }}</p>
                </div>
                <div class="metric-card metric-indigo">
                    <p class="text-sm font-semibold text-slate-500">Total Pelamar</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $totalApplicants }}</p>
                </div>
            </section>

            <section class="app-surface">
                <div class="app-toolbar">
                    <div>
                        <h3 class="app-section-title">Daftar Lowongan</h3>
                        <p class="app-section-subtitle">Cari dan pantau status lowongan aktif.</p>
                    </div>
                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input type="text" x-model="search" class="form-control w-full pl-8 sm:w-72" placeholder="Cari lowongan...">
                        </div>
                        <select x-model="status" class="form-control w-full sm:w-40">
                            <option value="all">Semua</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Review">Review</option>
                            <option value="Draft">Draft</option>
                            <option value="Tutup">Tutup</option>
                        </select>
                    </div>
                </div>

                <div class="app-table-wrap rounded-none border-0 shadow-none">
                    <table class="data-table admin-table-fixed">
                        <colgroup>
                            <col class="w-[24%]">
                            <col class="w-[14%]">
                            <col class="w-[12%]">
                            <col class="w-[10%]">
                            <col class="w-[11%]">
                            <col class="w-[12%]">
                            <col class="w-[17%]">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Posisi</th>
                                <th>Divisi</th>
                                <th>Tipe</th>
                                <th>Kuota</th>
                                <th>Pelamar</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody x-ref="jobsBody">
                            @forelse($openings as $opening)
                                @php
                                    $searchText = implode(' ', [
                                        $opening->title,
                                        $opening->division,
                                        $opening->employment_type,
                                        $opening->status,
                                        $opening->priority,
                                    ]);
                                    $badge = match ($opening->status) {
                                        'Aktif' => 'app-badge-success',
                                        'Review' => 'app-badge bg-sky-100 text-sky-700',
                                        'Tutup' => 'app-badge bg-rose-100 text-rose-700',
                                        default => 'app-badge-muted',
                                    };
                                @endphp
                                <tr class="data-table-row" x-show="matches(@js($searchText), @js($opening->status))" x-bind:data-visible="matches(@js($searchText), @js($opening->status))">
                                    <td>
                                        <p class="font-semibold text-slate-950">{{ $opening->title }}</p>
                                        <p class="mt-1 truncate text-xs text-slate-500">{{ $opening->description ?: 'Belum ada deskripsi posisi.' }}</p>
                                    </td>
                                    <td class="text-slate-600">{{ $opening->division }}</td>
                                    <td class="text-slate-600">{{ $opening->employment_type }}</td>
                                    <td class="font-semibold text-slate-950">{{ $opening->quota }}</td>
                                    <td>
                                        <span class="font-semibold text-slate-950">{{ $opening->candidates_count }}</span>
                                        <span class="text-xs text-slate-500">kandidat</span>
                                    </td>
                                    <td><span class="{{ $badge }}">{{ $opening->status }}</span></td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <a href="{{ route('recruitment.kandidat') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" title="Lihat kandidat">
                                                <i class="fas fa-users text-xs"></i>
                                            </a>
                                            <span class="app-badge-muted">{{ $opening->priority ?: 'Sedang' }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="app-empty-state">
                                            <div class="app-empty-state-icon"><i class="fas fa-briefcase"></i></div>
                                            <p class="mt-3 font-semibold text-slate-900">Belum ada lowongan</p>
                                            <p class="mt-1 text-sm text-slate-500">Tambahkan lowongan pertama agar kandidat bisa dipetakan ke posisi.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            @if($openings->isNotEmpty())
                                <tr x-show="!Array.from($refs.jobsBody.querySelectorAll('tr[data-visible]')).some(row => row.dataset.visible === 'true')" x-cloak>
                                    <td colspan="7">
                                        <div class="app-empty-state">
                                            <p class="font-semibold text-slate-900">Tidak ada lowongan yang cocok.</p>
                                            <p class="mt-1 text-sm text-slate-500">Ubah kata kunci atau status untuk melihat data lain.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div x-show="showCreate" x-cloak x-transition.opacity class="modal-backdrop" @keydown.escape.window="close()" @click.self="close()">
            <div class="modal-panel max-w-3xl">
                <div class="modal-header">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="modal-title">Tambah Lowongan</h3>
                            <p class="modal-subtitle">Lengkapi posisi, divisi, kriteria, dan kebutuhan kandidat.</p>
                        </div>
                        <button type="button" class="modal-close" @click="close()" aria-label="Tutup popup"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <form method="POST" action="{{ route('recruitment.openings.store') }}" class="modal-body space-y-4" data-submit-lock>
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label><span class="field-label">Nama Posisi</span><input name="title" value="{{ old('title') }}" x-ref="titleInput" class="form-control" placeholder="Teknisi Lapangan" required></label>
                        <label><span class="field-label">Divisi</span><input name="division" value="{{ old('division') }}" class="form-control" placeholder="Teknis" required></label>
                        <label><span class="field-label">Tipe Kerja</span><select name="employment_type" class="form-control" required>@foreach(['Full-time', 'Kontrak', 'Magang'] as $type)<option @selected(old('employment_type', 'Full-time') === $type)>{{ $type }}</option>@endforeach</select></label>
                        <label><span class="field-label">Kuota</span><input name="quota" value="{{ old('quota') }}" type="number" class="form-control" min="1" max="255" placeholder="3" required></label>
                        <label><span class="field-label">Status</span><select name="status" class="form-control" required>@foreach(['Aktif', 'Review', 'Draft', 'Tutup'] as $status)<option @selected(old('status', 'Draft') === $status)>{{ $status }}</option>@endforeach</select></label>
                        <label><span class="field-label">Prioritas</span><select name="priority" class="form-control">@foreach(['Sedang', 'Rendah', 'Tinggi'] as $priority)<option @selected(old('priority', 'Sedang') === $priority)>{{ $priority }}</option>@endforeach</select></label>
                        <label class="md:col-span-2"><span class="field-label">SLA</span><input name="sla" value="{{ old('sla', '14 hari') }}" class="form-control" placeholder="14 hari"></label>
                    </div>
                    <label><span class="field-label">Deskripsi Pekerjaan</span><textarea name="description" class="form-control" rows="4" placeholder="Tulis tanggung jawab utama posisi ini...">{{ old('description') }}</textarea></label>
                    <label><span class="field-label">Kriteria Kandidat</span><textarea name="criteria" class="form-control" rows="3" placeholder="Pengalaman, skill, pendidikan, atau sertifikasi...">{{ old('criteria') }}</textarea></label>
                    <div class="modal-footer -mx-6 -mb-6 mt-6">
                        <button type="button" class="btn-secondary-soft" @click="close()">Batal</button>
                        <button type="submit" class="btn-primary-soft">Simpan Lowongan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
