<x-app-layout>
    @php
        $totalWeight = $indicators->sum('weight');
        $remainingWeight = 100 - $totalWeight;
        $lockedCount = $indicators->where('is_locked', true)->count();
        $isReady = $indicators->count() > 0 && $totalWeight === 100;
    @endphp

    <div
        class="admin-shell"
        x-data="{
            showCreate: @js($errors->any()),
            showEdit: false,
            editing: {
                id: null,
                area: '',
                indicator: '',
                weight: '',
                target: '',
                measurement_method: '',
            },
            area: @js(old('area', '')),
            indicator: @js(old('indicator', '')),
            weight: @js(old('weight', '')),
            target: @js(old('target', '')),
            method: @js(old('measurement_method', '')),
            openCreate() {
                this.showCreate = true;
                this.$nextTick(() => this.$refs.areaInput?.focus());
            },
            reset() {
                this.area = '';
                this.indicator = '';
                this.weight = '';
                this.target = '';
                this.method = '';
            },
            close() {
                this.showCreate = false;
                this.reset();
            },
            openEdit(item) {
                this.editing = item;
                this.showEdit = true;
                this.$nextTick(() => this.$refs.editAreaInput?.focus());
            },
            closeEdit() {
                this.showEdit = false;
                this.editing = { id: null, area: '', indicator: '', weight: '', target: '', measurement_method: '' };
            }
        }"
    >
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Formulir KPI</h2>
                        <p class="admin-subtitle">Susun indikator penilaian, bobot, target, dan metode ukur agar evaluasi karyawan lebih konsisten.</p>
                    </div>

                    <button type="button" class="btn-primary-soft" @click="openCreate()">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Indikator
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_360px]">
                <div class="app-surface p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Kesiapan Formulir</p>
                            <p class="mt-2 text-3xl font-bold {{ $isReady ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $isReady ? 'Siap dipakai' : 'Perlu dilengkapi' }}
                            </p>
                            <p class="mt-2 text-sm text-slate-500">
                                {{ $isReady ? 'Bobot sudah 100%. Formulir bisa dikunci sebelum periode berjalan.' : ($remainingWeight > 0 ? 'Tambahkan '.$remainingWeight.'% bobot lagi agar penilaian lengkap.' : 'Kurangi '.abs($remainingWeight).'% bobot agar total kembali 100%.') }}
                            </p>
                        </div>
                        <div class="min-w-[220px] rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-end justify-between">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Total Bobot</span>
                                <span class="text-2xl font-black {{ $totalWeight === 100 ? 'text-emerald-700' : 'text-amber-700' }}">{{ $totalWeight }}%</span>
                            </div>
                            <div class="mt-3 h-3 overflow-hidden rounded-full bg-white">
                                <div class="h-full rounded-full {{ $totalWeight === 100 ? 'bg-emerald-600' : 'bg-amber-500' }}" data-progress-width="{{ min($totalWeight, 100) }}"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="app-surface p-5">
                    <h3 class="app-section-title">Checklist Cepat</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-600">Indikator tersedia</span>
                            <span class="font-bold {{ $indicators->count() > 0 ? 'text-emerald-700' : 'text-amber-700' }}">{{ $indicators->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-600">Bobot valid</span>
                            <span class="font-bold {{ $totalWeight === 100 ? 'text-emerald-700' : 'text-amber-700' }}">{{ $totalWeight === 100 ? 'Ya' : 'Belum' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-600">Terkunci</span>
                            <span class="font-bold text-slate-900">{{ $lockedCount }}/{{ $indicators->count() }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="app-surface">
                <div class="app-toolbar">
                    <div>
                        <h3 class="app-section-title">Komponen Penilaian</h3>
                        <p class="app-section-subtitle">Gunakan bobot yang jelas supaya skor akhir mudah diaudit.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('kpi.indicators.lock') }}" data-confirm="Kunci semua indikator KPI? Indikator yang dikunci tidak dapat diubah atau dihapus.">
                            @csrf
                            <button type="submit" class="btn-primary-soft disabled:cursor-not-allowed disabled:opacity-60" @disabled(!$isReady)>
                                <i class="fas fa-lock mr-2"></i>
                                Kunci Formulir
                            </button>
                        </form>
                    </div>
                </div>

                <div class="app-table-wrap rounded-none border-0 shadow-none">
                    <table class="data-table admin-table-fixed">
                        <thead>
                            <tr>
                                <th>Area</th>
                                <th>Indikator</th>
                                <th>Target</th>
                                <th>Metode Ukur</th>
                                <th class="text-right">Bobot</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($indicators as $item)
                                <tr class="data-table-row">
                                    <td><span class="app-badge-muted">{{ $item->area }}</span></td>
                                    <td><p class="font-semibold text-slate-900">{{ $item->indicator }}</p></td>
                                    <td class="text-slate-600">{{ $item->target }}</td>
                                    <td class="text-slate-600">{{ $item->measurement_method }}</td>
                                    <td class="text-right"><span class="font-bold text-slate-950">{{ $item->weight }}%</span></td>
                                    <td>
                                        <div class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                title="Edit indikator"
                                                @click="openEdit(@js([
                                                    'id' => $item->id,
                                                    'area' => $item->area,
                                                    'indicator' => $item->indicator,
                                                    'weight' => $item->weight,
                                                    'target' => $item->target,
                                                    'measurement_method' => $item->measurement_method,
                                                ]))"
                                                @disabled($item->is_locked)
                                            >
                                                <i class="fas fa-pen text-xs"></i>
                                            </button>
                                            <form method="POST" action="{{ route('kpi.indicators.destroy', $item) }}" data-confirm="Hapus indikator KPI ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-50" title="Hapus indikator" @disabled($item->is_locked)>
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada indikator KPI. Tambahkan indikator pertama secara manual.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_360px]">
                <div class="app-surface p-5">
                    <h3 class="app-section-title">Sumber Data Penilaian</h3>
                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                        @foreach($measurementMethods as $measurementMethod)
                            @php $methodCount = $indicators->where('measurement_method', $measurementMethod)->count(); @endphp
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm font-bold text-slate-900">{{ $measurementMethod }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $methodCount }} indikator memakai sumber ini</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="app-surface p-5">
                    <h3 class="app-section-title">Arahan Pengisian</h3>
                    <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        <p>Pastikan setiap indikator punya target yang bisa dibuktikan dari sumber data.</p>
                        <p>Kunci formulir hanya setelah total bobot tepat 100% dan semua indikator sudah disetujui.</p>
                    </div>
                </div>
            </section>
        </div>

        <div
            x-show="showCreate"
            x-cloak
            x-transition.opacity
            class="modal-backdrop"
            role="dialog"
            aria-modal="true"
            aria-labelledby="kpi-indicator-modal-title"
            @keydown.escape.window="close()"
            @click.self="close()"
        >
            <div class="modal-panel max-w-2xl">
                <div class="modal-header">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="kpi-indicator-modal-title" class="text-lg font-bold text-slate-950">Tambah Indikator KPI</h3>
                            <p class="mt-1 text-sm text-slate-500">Lengkapi indikator, target, dan bobot penilaian.</p>
                        </div>
                        <button type="button" class="modal-close" @click="close()" aria-label="Tutup popup">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('kpi.indicators.store') }}" class="modal-body space-y-4">
                    @csrf
                    @if($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="field-label">Area Penilaian</span>
                            <input type="text" name="area" x-model="area" x-ref="areaInput" class="form-control" placeholder="Contoh: Produktivitas" required>
                        </label>
                        <label>
                            <span class="field-label">Bobot (%)</span>
                            <input type="number" name="weight" x-model="weight" class="form-control" min="1" max="100" placeholder="30" required>
                        </label>
                    </div>
                    <label>
                        <span class="field-label">Indikator</span>
                        <input type="text" name="indicator" x-model="indicator" class="form-control" placeholder="Contoh: Penyelesaian tugas tepat waktu" required>
                    </label>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="field-label">Target</span>
                            <input type="text" name="target" x-model="target" class="form-control" placeholder=">= 90%" required>
                        </label>
                        <label>
                            <span class="field-label">Metode Ukur</span>
                            <select name="measurement_method" x-model="method" class="form-control" required>
                                <option value="">Pilih sumber data</option>
                                @foreach($measurementMethods as $measurementMethod)
                                    <option value="{{ $measurementMethod }}">{{ $measurementMethod }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="modal-footer -mx-6 -mb-6 mt-6">
                        <button type="button" class="btn-secondary-soft" @click="close()">Batal</button>
                        <button type="submit" class="btn-primary-soft">Simpan Indikator</button>
                    </div>
                </form>
            </div>
        </div>

        <div
            x-show="showEdit"
            x-cloak
            x-transition.opacity
            class="modal-backdrop"
            role="dialog"
            aria-modal="true"
            aria-labelledby="kpi-indicator-edit-modal-title"
            @keydown.escape.window="closeEdit()"
            @click.self="closeEdit()"
        >
            <div class="modal-panel max-w-2xl">
                <div class="modal-header">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="kpi-indicator-edit-modal-title" class="modal-title">Edit Indikator KPI</h3>
                            <p class="modal-subtitle">Perbarui indikator, target, dan bobot penilaian.</p>
                        </div>
                        <button type="button" class="modal-close" @click="closeEdit()" aria-label="Tutup popup">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <form method="POST" class="modal-body space-y-4" :action="editing ? `/kpi/formulir/${editing.id}` : '#'" data-submit-lock>
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="field-label">Area Penilaian</span>
                            <input type="text" name="area" x-model="editing.area" x-ref="editAreaInput" class="form-control" required>
                        </label>
                        <label>
                            <span class="field-label">Bobot (%)</span>
                            <input type="number" name="weight" x-model="editing.weight" class="form-control" min="1" max="100" required>
                        </label>
                    </div>
                    <label>
                        <span class="field-label">Indikator</span>
                        <input type="text" name="indicator" x-model="editing.indicator" class="form-control" required>
                    </label>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="field-label">Target</span>
                            <input type="text" name="target" x-model="editing.target" class="form-control" required>
                        </label>
                        <label>
                            <span class="field-label">Metode Ukur</span>
                            <select name="measurement_method" x-model="editing.measurement_method" class="form-control" required>
                                @foreach($measurementMethods as $measurementMethod)
                                    <option value="{{ $measurementMethod }}">{{ $measurementMethod }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="modal-footer -mx-6 -mb-6 mt-6">
                        <button type="button" class="btn-secondary-soft" @click="closeEdit()">Batal</button>
                        <button type="submit" class="btn-primary-soft">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
