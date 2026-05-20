<x-app-layout>
    @php
        $activeSchedule = $schedules->firstWhere('status', 'Berjalan');
        $nextSchedule = $schedules->firstWhere('status', 'Terjadwal');
        $draftCount = $schedules->where('status', 'Draft')->count();
        $doneCount = $schedules->where('status', 'Selesai')->count();
    @endphp

    <div
        class="admin-shell"
        x-data="{
            showCreate: @js($errors->any()),
            showEdit: false,
            editing: {
                id: null,
                period: '',
                division: '',
                start_date: '',
                end_date: '',
                status: 'Draft',
                progress: 0,
                notes: '',
            },
            openCreate() {
                this.showCreate = true;
                this.$nextTick(() => this.$refs.periodInput?.focus());
            },
            close() {
                this.showCreate = false;
            },
            openEdit(schedule) {
                this.editing = schedule;
                this.showEdit = true;
                this.$nextTick(() => this.$refs.editPeriodInput?.focus());
            },
            closeEdit() {
                this.showEdit = false;
                this.editing = { id: null, period: '', division: '', start_date: '', end_date: '', status: 'Draft', progress: 0, notes: '' };
            }
        }"
    >
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Jadwal KPI</h2>
                        <p class="admin-subtitle">Atur periode evaluasi, batas pengisian, dan divisi yang masuk penilaian.</p>
                    </div>
                    <button type="button" class="btn-primary-soft" @click="openCreate()">
                        <i class="fas fa-calendar-plus mr-2"></i>
                        Buat Jadwal
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
                            <p class="text-sm font-semibold text-slate-500">Periode Aktif</p>
                            <p class="mt-2 text-3xl font-bold text-slate-950">{{ $activeSchedule?->period ?? 'Belum Ada' }}</p>
                            <p class="mt-2 text-sm text-slate-500">{{ $activeSchedule ? $activeSchedule->start_date->format('d M Y').' - '.$activeSchedule->end_date->format('d M Y').' untuk '.$activeSchedule->division : 'Buat jadwal berjalan agar evaluasi punya periode kerja.' }}</p>
                        </div>
                        <div class="min-w-[220px] rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-end justify-between">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Progress</span>
                                <span class="text-2xl font-black text-emerald-700">{{ $activeSchedule?->progress ?? 0 }}%</span>
                            </div>
                            <div class="mt-3 h-3 overflow-hidden rounded-full bg-white">
                                <div class="h-full rounded-full bg-emerald-600" data-progress-width="{{ $activeSchedule?->progress ?? 0 }}"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="app-surface p-5">
                    <h3 class="app-section-title">Ringkasan Jadwal</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-600">Total periode</span>
                            <span class="font-bold text-slate-900">{{ $schedules->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-600">Draft</span>
                            <span class="font-bold text-amber-700">{{ $draftCount }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-600">Selesai</span>
                            <span class="font-bold text-emerald-700">{{ $doneCount }}</span>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3 text-xs font-semibold text-slate-500">
                            Berikutnya: {{ $nextSchedule?->period ?? 'belum ada periode terjadwal' }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-[360px_1fr]">
                <div class="app-surface p-5">
                    <h3 class="app-section-title">Panduan Operasional</h3>
                    <div class="mt-5 space-y-5">
                        <div class="grid grid-cols-[36px_1fr] gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $activeSchedule ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }} text-sm font-bold">1</div>
                            <div>
                                <p class="font-semibold text-slate-950">Tentukan Periode</p>
                                <p class="mt-1 text-sm text-slate-500">Tetapkan tanggal mulai, tanggal selesai, dan divisi peserta.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-[36px_1fr] gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $activeSchedule && $activeSchedule->progress > 0 ? 'bg-sky-600 text-white' : 'bg-slate-200 text-slate-600' }} text-sm font-bold">2</div>
                            <div>
                                <p class="font-semibold text-slate-950">Pantau Pengisian</p>
                                <p class="mt-1 text-sm text-slate-500">Gunakan progress untuk melihat kesiapan rekap evaluasi.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-[36px_1fr] gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $activeSchedule?->status === 'Selesai' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }} text-sm font-bold">3</div>
                            <div>
                                <p class="font-semibold text-slate-950">Finalisasi</p>
                                <p class="mt-1 text-sm text-slate-500">Tutup periode setelah seluruh evaluasi selesai direview.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="app-surface">
                    <div class="app-toolbar">
                        <div>
                            <h3 class="app-section-title">Daftar Jadwal</h3>
                            <p class="app-section-subtitle">Pantau jadwal KPI per periode dan divisi.</p>
                        </div>
                    </div>

                    <div class="app-table-wrap rounded-none border-0 shadow-none">
                        <table class="data-table admin-table-fixed">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Rentang</th>
                                    <th>Divisi</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    @php
                                        $badge = match ($schedule->status) {
                                            'Berjalan' => 'app-badge bg-sky-100 text-sky-700',
                                            'Terjadwal' => 'app-badge-success',
                                            'Selesai' => 'app-badge bg-emerald-100 text-emerald-700',
                                            default => 'app-badge-muted',
                                        };
                                    @endphp
                                    <tr class="data-table-row">
                                        <td class="font-semibold text-slate-950">{{ $schedule->period }}</td>
                                        <td class="text-slate-600">{{ $schedule->start_date->format('d M Y') }} - {{ $schedule->end_date->format('d M Y') }}</td>
                                        <td class="text-slate-600">{{ $schedule->division }}</td>
                                        <td><span class="{{ $badge }}">{{ $schedule->status }}</span></td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full bg-emerald-600" data-progress-width="{{ $schedule->progress }}"></div>
                                                </div>
                                                <span class="text-xs font-semibold text-slate-500">{{ $schedule->progress }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50"
                                                    title="Edit jadwal"
                                                    @click="openEdit(@js([
                                                        'id' => $schedule->id,
                                                        'period' => $schedule->period,
                                                        'division' => $schedule->division,
                                                        'start_date' => $schedule->start_date->format('Y-m-d'),
                                                        'end_date' => $schedule->end_date->format('Y-m-d'),
                                                        'status' => $schedule->status,
                                                        'progress' => $schedule->progress,
                                                        'notes' => $schedule->notes,
                                                    ]))"
                                                >
                                                    <i class="fas fa-pen text-xs"></i>
                                                </button>
                                                <form method="POST" action="{{ route('kpi.schedules.destroy', $schedule) }}" data-confirm="Hapus jadwal KPI ini?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50" title="Hapus jadwal">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada jadwal KPI. Buat jadwal pertama secara manual.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
            aria-labelledby="kpi-schedule-modal-title"
            @keydown.escape.window="close()"
            @click.self="close()"
        >
            <div class="modal-panel max-w-2xl">
                <div class="modal-header">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="kpi-schedule-modal-title" class="text-lg font-bold text-slate-950">Buat Jadwal KPI</h3>
                            <p class="mt-1 text-sm text-slate-500">Tentukan periode, rentang pengisian, dan divisi peserta.</p>
                        </div>
                        <button type="button" class="modal-close" @click="close()" aria-label="Tutup popup">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('kpi.schedules.store') }}" class="modal-body space-y-4">
                    @csrf
                    @if($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="field-label">Nama Periode</span>
                            <input name="period" type="text" value="{{ old('period') }}" x-ref="periodInput" class="form-control" placeholder="Mei 2026" required>
                        </label>
                        <label>
                            <span class="field-label">Divisi</span>
                            <select name="division" class="form-control" required>
                                @foreach($divisionOptions as $division)
                                    <option @selected(old('division', 'Semua Divisi') === $division)>{{ $division }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="field-label">Tanggal Mulai</span>
                            <input name="start_date" type="date" value="{{ old('start_date') }}" class="form-control" required>
                        </label>
                        <label>
                            <span class="field-label">Tanggal Selesai</span>
                            <input name="end_date" type="date" value="{{ old('end_date') }}" class="form-control" required>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="field-label">Status</span>
                            <select name="status" class="form-control" required>
                                @foreach(['Draft', 'Terjadwal', 'Berjalan', 'Selesai'] as $status)
                                    <option @selected(old('status', 'Draft') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span class="field-label">Progress (%)</span>
                            <input name="progress" type="number" min="0" max="100" value="{{ old('progress') }}" class="form-control" placeholder="0">
                        </label>
                    </div>
                    <label>
                        <span class="field-label">Catatan Jadwal</span>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Instruksi atau fokus evaluasi periode ini...">{{ old('notes') }}</textarea>
                    </label>
                    <div class="modal-footer -mx-6 -mb-6 mt-6">
                        <button type="button" class="btn-secondary-soft" @click="close()">Batal</button>
                        <button type="submit" class="btn-primary-soft">Simpan Jadwal</button>
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
            aria-labelledby="kpi-schedule-edit-modal-title"
            @keydown.escape.window="closeEdit()"
            @click.self="closeEdit()"
        >
            <div class="modal-panel max-w-2xl">
                <div class="modal-header">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="kpi-schedule-edit-modal-title" class="modal-title">Edit Jadwal KPI</h3>
                            <p class="modal-subtitle">Perbarui periode, rentang pengisian, dan status jadwal.</p>
                        </div>
                        <button type="button" class="modal-close" @click="closeEdit()" aria-label="Tutup popup">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <form method="POST" class="modal-body space-y-4" :action="editing ? `/kpi/jadwal/${editing.id}` : '#'" data-submit-lock>
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="field-label">Nama Periode</span>
                            <input name="period" type="text" x-model="editing.period" x-ref="editPeriodInput" class="form-control" required>
                        </label>
                        <label>
                            <span class="field-label">Divisi</span>
                            <select name="division" x-model="editing.division" class="form-control" required>
                                @foreach($divisionOptions as $division)
                                    <option>{{ $division }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="field-label">Tanggal Mulai</span>
                            <input name="start_date" type="date" x-model="editing.start_date" class="form-control" required>
                        </label>
                        <label>
                            <span class="field-label">Tanggal Selesai</span>
                            <input name="end_date" type="date" x-model="editing.end_date" class="form-control" required>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label>
                            <span class="field-label">Status</span>
                            <select name="status" x-model="editing.status" class="form-control" required>
                                @foreach(['Draft', 'Terjadwal', 'Berjalan', 'Selesai'] as $status)
                                    <option>{{ $status }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span class="field-label">Progress (%)</span>
                            <input name="progress" type="number" min="0" max="100" x-model="editing.progress" class="form-control">
                        </label>
                    </div>
                    <label>
                        <span class="field-label">Catatan Jadwal</span>
                        <textarea name="notes" x-model="editing.notes" class="form-control" rows="3"></textarea>
                    </label>
                    <div class="modal-footer -mx-6 -mb-6 mt-6">
                        <button type="button" class="btn-secondary-soft" @click="closeEdit()">Batal</button>
                        <button type="submit" class="btn-primary-soft">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
