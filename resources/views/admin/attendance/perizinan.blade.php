<x-app-layout>
    @php
        $pendingCount = $permissions->where('status', 'pending')->count();
        $approvedCount = $permissions->where('status', 'approved')->count();
        $rejectedCount = $permissions->where('status', 'rejected')->count();
    @endphp

    <div
        class="admin-shell"
        x-data="{
            showHistory: false,
            search: '',
            type: 'all',
            matches(value, rowType) {
                const keyword = this.search.trim().toLowerCase();
                const matchText = keyword === '' || value.toLowerCase().includes(keyword);
                const matchType = this.type === 'all' || this.type === rowType;

                return matchText && matchType;
            }
        }"
        @keydown.escape.window="showHistory = false"
    >
        <div class="admin-container admin-container-fluid">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Persetujuan Perizinan</h2>
                        <p class="admin-subtitle">Validasi pengajuan sakit, izin, dan cuti karyawan.</p>
                    </div>

                    <button type="button" @click="showHistory = true" class="btn-secondary-soft">
                        <i class="fas fa-clock-rotate-left mr-2"></i> Riwayat Perizinan
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div id="permission-success" class="alert-success flex items-center justify-between gap-4">
                    <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                    <button type="button" data-dismiss="#permission-success" class="text-emerald-600 hover:text-emerald-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Menunggu</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $pendingCount }}</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Disetujui</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $approvedCount }}</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Ditolak</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $rejectedCount }}</p>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-header flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="admin-card-title">Antrian Pengajuan</h3>
                        <p class="mt-1 text-xs text-slate-500">Pengajuan yang masih menunggu keputusan.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input type="text" x-model="search" placeholder="Cari karyawan/alasan..." class="app-input w-full pl-8 sm:w-64">
                        </div>
                        <select x-model="type" class="app-input w-full sm:w-36">
                            <option value="all">Semua</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                            <option value="cuti">Cuti</option>
                        </select>
                        <span class="app-badge-warning">{{ $pendingCount }} pending</span>
                    </div>
                </div>

                <div class="app-table-wrap rounded-none border-0 shadow-none">
                    <table class="data-table admin-table-xl permission-queue-table">
                        <colgroup>
                            <col class="w-[20%]">
                            <col class="w-[10%]">
                            <col class="w-[17%]">
                            <col class="w-[23%]">
                            <col class="w-[9%]">
                            <col class="w-[10%]">
                            <col class="w-[11%]">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Tipe</th>
                                <th>Periode</th>
                                <th>Alasan</th>
                                <th>Foto</th>
                                <th>Dokumen</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody x-ref="pendingBody">
                            @foreach($permissions->where('status', 'pending') as $permission)
                                <tr
                                    class="data-table-row"
                                    x-show="matches(@js(($permission->user->name ?? '').' '.($permission->user->division->name ?? '').' '.$permission->reason), @js($permission->type))"
                                    x-bind:data-visible="matches(@js(($permission->user->name ?? '').' '.($permission->user->division->name ?? '').' '.$permission->reason), @js($permission->type))"
                                >
                                    <td class="min-w-56">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-sm font-bold text-white">
                                                {{ strtoupper(substr($permission->user->name ?? '-', 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-950">{{ $permission->user->name ?? '-' }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $permission->user->division->name ?? 'Tanpa Divisi' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="app-badge {{ $permission->type === 'sakit' ? 'bg-rose-100 text-rose-700' : ($permission->type === 'cuti' ? 'app-badge-success' : 'bg-sky-100 text-sky-700') }}">
                                            {{ ucfirst($permission->type) }}
                                        </span>
                                    </td>
                                    <td class="min-w-52 text-slate-700">
                                        {{ \Carbon\Carbon::parse($permission->start_date)->format('d M Y') }}
                                        <span class="text-slate-400">s/d</span>
                                        {{ \Carbon\Carbon::parse($permission->end_date)->format('d M Y') }}
                                    </td>
                                    <td><div class="admin-table-text">{{ $permission->reason ?: '-' }}</div></td>
                                    <td>
                                        @if($permission->attachment_photo)
                                            <x-lightbox-image 
                                                src="{{ asset($permission->attachment_photo) }}"
                                                alt="Foto lampiran"
                                                class="h-12 w-12 rounded-lg border border-slate-200 object-cover shadow-sm"
                                            />
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($permission->attachment_file)
                                            <a href="{{ asset($permission->attachment_file) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-100">
                                                <i class="fas fa-file-pdf"></i> Dokumen
                                            </a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <form action="{{ route('admin.presence.approve', $permission->id) }}" method="POST" data-confirm="Setujui pengajuan {{ $permission->user->name ?? 'ini' }}?" data-submit-lock>
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                                                    <i class="fas fa-check"></i> Setuju
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.presence.reject', $permission->id) }}" method="POST" data-confirm="Tolak pengajuan {{ $permission->user->name ?? 'ini' }}?" data-submit-lock>
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-700">
                                                    <i class="fas fa-xmark"></i> Tolak
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr x-show="!Array.from($refs.pendingBody.querySelectorAll('tr[data-visible]')).some(row => row.dataset.visible === 'true')" x-cloak>
                                <td colspan="7" class="permission-table-empty px-5 py-14 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                            <i class="fas fa-circle-check text-xl"></i>
                                        </div>
                                        <p class="mt-3 font-semibold text-slate-900">Tidak ada pengajuan pending</p>
                                        <p class="mt-1 text-sm text-slate-500">Semua perizinan sudah diproses.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div x-show="showHistory" x-cloak x-transition.opacity class="modal-backdrop">
            <div
                @click.outside="showHistory = false"
                x-transition:enter="transform transition ease-out duration-200"
                x-transition:enter-start="scale-95 opacity-0"
                x-transition:enter-end="scale-100 opacity-100"
                x-transition:leave="transform transition ease-in duration-150"
                x-transition:leave-start="scale-100 opacity-100"
                x-transition:leave-end="scale-95 opacity-0"
                class="modal-panel max-w-6xl"
            >
                <div class="modal-header flex items-center justify-between">
                    <div>
                        <h3 class="modal-title">Riwayat Perizinan</h3>
                        <p class="modal-subtitle">Semua pengajuan yang sudah diproses.</p>
                    </div>
                    <button type="button" @click="showHistory = false" class="modal-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="app-table-wrap rounded-none border-0 shadow-none">
                    <table class="data-table admin-table-wide">
                        <thead>
                            <tr>
                                <th>Karyawan</th>
                                <th>Tipe</th>
                                <th>Periode</th>
                                <th>Lampiran</th>
                                <th>Status</th>
                                <th>Diproses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions->whereIn('status', ['approved', 'rejected']) as $history)
                                <tr class="data-table-row">
                                    <td>
                                        <p class="font-semibold text-slate-950">{{ $history->user->name ?? '-' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $history->user->division->name ?? 'Tanpa Divisi' }}</p>
                                    </td>
                                    <td>
                                        <span class="app-badge-muted">{{ ucfirst($history->type) }}</span>
                                    </td>
                                    <td class="min-w-52">
                                        {{ \Carbon\Carbon::parse($history->start_date)->format('d M Y') }}
                                        <span class="text-slate-400">s/d</span>
                                        {{ \Carbon\Carbon::parse($history->end_date)->format('d M Y') }}
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            @if($history->attachment_photo)
                                                <x-lightbox-image 
                                                    src="{{ asset($history->attachment_photo) }}"
                                                    alt="Foto lampiran"
                                                    class="h-10 w-14 rounded border border-slate-200 object-cover"
                                                />
                                            @endif
                                            @if($history->attachment_file)
                                                <a href="{{ asset($history->attachment_file) }}" target="_blank" class="btn-secondary-soft px-3 py-1.5 text-xs">Dokumen</a>
                                            @endif
                                            @if(!$history->attachment_photo && !$history->attachment_file)
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="{{ $history->status === 'approved' ? 'app-badge-success' : 'app-badge bg-rose-100 text-rose-700' }}">
                                            {{ $history->status }}
                                        </span>
                                    </td>
                                    <td class="text-slate-600">{{ $history->updated_at ? $history->updated_at->format('d M Y H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-slate-500">Belum ada riwayat perizinan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" @click="showHistory = false" class="btn-secondary-soft">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
