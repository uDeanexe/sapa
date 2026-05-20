<x-app-layout>
    @php
        $evaluationRows = $evaluations->map(fn ($evaluation) => [
            'id' => $evaluation->id,
            'name' => $evaluation->employee_name,
            'division' => $evaluation->division,
            'period' => $evaluation->period,
            'score' => $evaluation->score,
            'grade' => $evaluation->grade,
            'status' => $evaluation->status,
            'note' => $evaluation->note,
        ])->values();
        $average = round($evaluations->avg('score') ?? 0, 1);
        $finalCount = $evaluations->where('status', 'Final')->count();
        $topScore = $evaluations->max('score') ?? 0;
        $reviewCount = $evaluations->whereIn('status', ['Draft', 'Review'])->count();
        $gradeCounts = collect(['A', 'B', 'C', 'D'])->mapWithKeys(fn ($grade) => [$grade => $evaluations->where('grade', $grade)->count()]);
        $gradePercents = $gradeCounts->map(fn ($count) => $evaluations->count() > 0 ? round(($count / $evaluations->count()) * 100) : 0);
        $dominantGrade = $gradeCounts->sortDesc()->keys()->first() ?? '-';
    @endphp

    <div
        class="admin-shell"
        x-data="{
            showCreate: @js($errors->any()),
            showEdit: false,
            editing: {
                id: null,
                name: '',
                division: '',
                period: '',
                score: 0,
                status: 'Draft',
                note: '',
            },
            search: '',
            status: 'all',
            selected: null,
            rows: @js($evaluationRows),
            openCreate() {
                this.showCreate = true;
                this.$nextTick(() => this.$refs.employeeInput?.focus());
            },
            close() { this.showCreate = false },
            openEdit(row) {
                this.editing = { ...row };
                this.showEdit = true;
                this.$nextTick(() => this.$refs.editEmployeeInput?.focus());
            },
            closeEdit() {
                this.showEdit = false;
                this.editing = { id: null, name: '', division: '', period: '', score: 0, status: 'Draft', note: '' };
            },
            matches(row) {
                const keyword = this.search.trim().toLowerCase();
                const text = [row.name, row.division, row.period, row.grade, row.status].join(' ').toLowerCase();
                const textMatch = keyword === '' || text.includes(keyword);
                const statusMatch = this.status === 'all' || row.status === this.status;

                return textMatch && statusMatch;
            }
        }"
    >
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Evaluasi KPI</h2>
                        <p class="admin-subtitle">Lihat hasil penilaian, status review, nilai akhir, dan catatan pembinaan karyawan.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('kpi.formulir') }}" class="btn-secondary-soft">
                            <i class="fas fa-list-check mr-2"></i>
                            Formulir
                        </a>
                        <button type="button" class="btn-primary-soft" @click="openCreate()">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Evaluasi
                        </button>
                    </div>
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
                            <p class="text-sm font-semibold text-slate-500">Analisa Evaluasi</p>
                            <p class="mt-2 text-3xl font-bold text-slate-950">{{ $average }}</p>
                            <p class="mt-2 text-sm text-slate-500">
                                Rata-rata nilai dari {{ $evaluations->count() }} evaluasi. Grade terbanyak: {{ $dominantGrade }}.
                            </p>
                        </div>
                        <div class="grid min-w-[260px] grid-cols-2 gap-3">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Tertinggi</p>
                                <p class="mt-2 text-2xl font-black text-emerald-800">{{ $topScore }}</p>
                            </div>
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-amber-700">Review</p>
                                <p class="mt-2 text-2xl font-black text-amber-800">{{ $reviewCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="app-surface p-5">
                    <h3 class="app-section-title">Status Rekap</h3>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-600">Total evaluasi</span>
                            <span class="font-bold text-slate-900">{{ $evaluations->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-600">Final</span>
                            <span class="font-bold text-emerald-700">{{ $finalCount }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-slate-600">Belum final</span>
                            <span class="font-bold text-amber-700">{{ $reviewCount }}</span>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3 text-xs font-semibold text-slate-500">
                            {{ $reviewCount > 0 ? 'Prioritas: selesaikan review sebelum rekap dipakai.' : 'Semua evaluasi sudah final.' }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_360px]">
                <div class="app-surface">
                    <div class="app-toolbar">
                        <div>
                            <h3 class="app-section-title">Rekap Evaluasi</h3>
                            <p class="app-section-subtitle">Klik baris untuk melihat detail catatan evaluasi.</p>
                        </div>
                        <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input type="text" x-model="search" class="form-control w-full pl-8 sm:w-64" placeholder="Cari karyawan...">
                            </div>
                            <select x-model="status" class="form-control w-full sm:w-36">
                                <option value="all">Semua</option>
                                <option value="Draft">Draft</option>
                                <option value="Review">Review</option>
                                <option value="Final">Final</option>
                            </select>
                        </div>
                    </div>

                    <div class="app-table-wrap rounded-none border-0 shadow-none">
                        <table class="data-table admin-table-fixed">
                            <thead>
                                <tr>
                                    <th>Karyawan</th>
                                    <th>Divisi</th>
                                    <th>Periode</th>
                                    <th>Nilai</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in rows" :key="row.id">
                                    <tr x-show="matches(row)" class="data-table-row cursor-pointer" @click="selected = row">
                                        <td>
                                            <p class="font-semibold text-slate-950" x-text="row.name"></p>
                                            <p class="mt-1 text-xs text-slate-500" x-text="row.note"></p>
                                        </td>
                                        <td class="text-slate-600" x-text="row.division"></td>
                                        <td class="text-slate-600" x-text="row.period"></td>
                                        <td><span class="text-lg font-bold text-slate-950" x-text="row.score"></span></td>
                                        <td>
                                            <span class="app-badge-success" x-show="row.grade === 'A'" x-text="row.grade"></span>
                                            <span class="app-badge bg-sky-100 text-sky-700" x-show="row.grade === 'B'" x-text="row.grade"></span>
                                            <span class="app-badge-warning" x-show="row.grade === 'C'" x-text="row.grade"></span>
                                            <span class="app-badge bg-rose-100 text-rose-700" x-show="row.grade === 'D'" x-text="row.grade"></span>
                                        </td>
                                        <td>
                                            <span class="app-badge-success" x-show="row.status === 'Final'" x-text="row.status"></span>
                                            <span class="app-badge bg-sky-100 text-sky-700" x-show="row.status === 'Review'" x-text="row.status"></span>
                                            <span class="app-badge-muted" x-show="row.status === 'Draft'" x-text="row.status"></span>
                                        </td>
                                        <td>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" title="Lihat detail" @click.stop="selected = row">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </button>
                                                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50" title="Edit nilai" @click.stop="openEdit(row)">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="rows.filter((row) => matches(row)).length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada evaluasi yang cocok.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="app-surface p-5">
                        <h3 class="app-section-title">Distribusi Nilai</h3>
                        <div class="mt-4 space-y-4">
                            @foreach(['A' => 'bg-emerald-600', 'B' => 'bg-sky-600', 'C' => 'bg-amber-500', 'D' => 'bg-rose-500'] as $grade => $barColor)
                                <div>
                                    <div class="mb-1 flex justify-between text-xs font-semibold text-slate-500">
                                        <span>Grade {{ $grade }}</span><span>{{ $gradePercents[$grade] }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full {{ $barColor }}" data-progress-width="{{ $gradePercents[$grade] }}"></div></div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="app-surface p-5">
                        <h3 class="app-section-title">Detail Terpilih</h3>
                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4" x-show="!selected">
                            <p class="text-sm leading-6 text-slate-500">Pilih salah satu baris evaluasi untuk melihat catatan dan ringkasan nilai.</p>
                        </div>
                        <div class="mt-4 space-y-4" x-show="selected" x-cloak>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Karyawan</p>
                                <p class="mt-1 font-bold text-slate-950" x-text="selected?.name"></p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs text-slate-500">Nilai</p>
                                    <p class="mt-1 text-2xl font-bold text-slate-950" x-text="selected?.score"></p>
                                </div>
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs text-slate-500">Grade</p>
                                    <p class="mt-1 text-2xl font-bold text-emerald-700" x-text="selected?.grade"></p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Catatan</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600" x-text="selected?.note"></p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" class="btn-secondary-soft flex-1" @click="openEdit(selected)">
                                    <i class="fas fa-pen mr-2"></i>
                                    Edit
                                </button>
                                <form method="POST" class="flex-1" :action="selected ? `/kpi/evaluasi/${selected.id}/final` : '#'" data-confirm="Finalkan evaluasi terpilih?">
                                    @csrf
                                    <button type="submit" class="btn-primary-soft w-full" :disabled="!selected || selected.status === 'Final'">
                                        <i class="fas fa-lock mr-2"></i>
                                        Final
                                    </button>
                                </form>
                            </div>
                            <form method="POST" :action="selected ? `/kpi/evaluasi/${selected.id}` : '#'" data-confirm="Hapus evaluasi terpilih?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-100" :disabled="!selected">
                                    <i class="fas fa-trash mr-2"></i>
                                    Hapus Evaluasi
                                </button>
                            </form>
                        </div>
                    </div>
                </aside>
            </section>
        </div>

        <div
            x-show="showCreate"
            x-cloak
            x-transition.opacity
            class="modal-backdrop"
            role="dialog"
            aria-modal="true"
            aria-labelledby="kpi-evaluation-modal-title"
            @keydown.escape.window="close()"
            @click.self="close()"
        >
            <div class="modal-panel max-w-2xl">
                <div class="modal-header">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="kpi-evaluation-modal-title" class="text-lg font-bold text-slate-950">Tambah Evaluasi KPI</h3>
                            <p class="mt-1 text-sm text-slate-500">Input nilai akhir, status review, dan catatan evaluasi karyawan.</p>
                        </div>
                        <button type="button" class="modal-close" @click="close()" aria-label="Tutup popup">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <form method="POST" action="{{ route('kpi.evaluations.store') }}" class="modal-body space-y-4">
                    @csrf
                    @if($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label><span class="field-label">Nama Karyawan</span><input name="employee_name" value="{{ old('employee_name') }}" x-ref="employeeInput" class="form-control" placeholder="Andi Pratama" required></label>
                        <label><span class="field-label">Divisi</span><input name="division" value="{{ old('division') }}" class="form-control" placeholder="Teknis" required></label>
                        <label><span class="field-label">Periode</span><input name="period" value="{{ old('period') }}" class="form-control" placeholder="Mei 2026" required></label>
                        <label><span class="field-label">Nilai</span><input name="score" type="number" min="0" max="100" value="{{ old('score') }}" class="form-control" placeholder="90" required></label>
                        <label><span class="field-label">Status</span><select name="status" class="form-control">@foreach(['Draft', 'Review', 'Final'] as $status)<option @selected(old('status', 'Draft') === $status)>{{ $status }}</option>@endforeach</select></label>
                    </div>
                    <label><span class="field-label">Catatan</span><textarea name="note" class="form-control" rows="4" placeholder="Catatan evaluasi, apresiasi, atau arahan pembinaan...">{{ old('note') }}</textarea></label>
                    <div class="modal-footer -mx-6 -mb-6 mt-6">
                        <button type="button" class="btn-secondary-soft" @click="close()">Batal</button>
                        <button type="submit" class="btn-primary-soft">Simpan Evaluasi</button>
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
            aria-labelledby="kpi-evaluation-edit-modal-title"
            @keydown.escape.window="closeEdit()"
            @click.self="closeEdit()"
        >
            <div class="modal-panel max-w-2xl">
                <div class="modal-header">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="kpi-evaluation-edit-modal-title" class="modal-title">Edit Evaluasi KPI</h3>
                            <p class="modal-subtitle">Perbarui nilai, status review, dan catatan evaluasi.</p>
                        </div>
                        <button type="button" class="modal-close" @click="closeEdit()" aria-label="Tutup popup">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <form method="POST" class="modal-body space-y-4" :action="editing ? `/kpi/evaluasi/${editing.id}` : '#'" data-submit-lock>
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <label><span class="field-label">Nama Karyawan</span><input name="employee_name" x-model="editing.name" x-ref="editEmployeeInput" class="form-control" required></label>
                        <label><span class="field-label">Divisi</span><input name="division" x-model="editing.division" class="form-control" required></label>
                        <label><span class="field-label">Periode</span><input name="period" x-model="editing.period" class="form-control" required></label>
                        <label><span class="field-label">Nilai</span><input name="score" type="number" min="0" max="100" x-model="editing.score" class="form-control" required></label>
                        <label><span class="field-label">Status</span><select name="status" x-model="editing.status" class="form-control">@foreach(['Draft', 'Review', 'Final'] as $status)<option>{{ $status }}</option>@endforeach</select></label>
                    </div>
                    <label><span class="field-label">Catatan</span><textarea name="note" x-model="editing.note" class="form-control" rows="4"></textarea></label>
                    <div class="modal-footer -mx-6 -mb-6 mt-6">
                        <button type="button" class="btn-secondary-soft" @click="closeEdit()">Batal</button>
                        <button type="submit" class="btn-primary-soft">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
