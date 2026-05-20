<x-app-layout>
    @php
        $totalPendingIn = $presences->where('is_approved', 'pending')->count();
        $totalPendingOut = $presences->filter(fn ($presence) =>
            $presence->check_out && ($presence->is_approved_out ?? 'pending') === 'pending'
        )->count();
        $grandTotal = $totalPendingIn + $totalPendingOut;
        $rowNumber = 1;
    @endphp

    <div
        class="admin-shell"
        x-data="{
            search: '',
            type: 'all',
            matches(value, rowType) {
                const keyword = this.search.trim().toLowerCase();
                const matchText = keyword === '' || value.toLowerCase().includes(keyword);
                const matchType = this.type === 'all' || this.type === rowType;

                return matchText && matchType;
            }
        }"
    >
        <div class="admin-container admin-container-fluid">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Persetujuan Presensi</h2>
                        <p class="admin-subtitle">Review absensi manual untuk WFH atau lokasi di luar radius kantor.</p>
                    </div>

                    <a href="{{ route('admin.presence.history') }}" class="btn-secondary-soft">
                        <i class="fas fa-clock-rotate-left mr-2"></i> Riwayat
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert-success flex items-center justify-between gap-4">
                    <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                    <button type="button" onclick="this.closest('.alert-success').remove()" class="text-emerald-600 hover:text-emerald-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="app-surface p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Total Menunggu</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <i class="fas fa-hourglass-half"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $grandTotal }}</p>
                </div>

                <div class="app-surface p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Check-In</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                            <i class="fas fa-right-to-bracket"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $totalPendingIn }}</p>
                </div>

                <div class="app-surface p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Check-Out</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                            <i class="fas fa-right-from-bracket"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $totalPendingOut }}</p>
                </div>
            </section>

            <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                <i class="fas fa-circle-info mr-2"></i>
                Setelah disetujui atau ditolak, data otomatis hilang dari halaman ini dan masuk ke Riwayat Presensi.
            </div>

            <section class="admin-card">
                <div class="admin-card-header flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="admin-card-title">Daftar Approval</h3>
                        <p class="mt-1 text-xs text-slate-500">Data absensi yang membutuhkan persetujuan manual.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        @if($grandTotal > 0)
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input type="text" x-model="search" placeholder="Cari karyawan/catatan..." class="app-input w-full pl-8 sm:w-64">
                            </div>
                            <select x-model="type" class="app-input w-full sm:w-36">
                                <option value="all">Semua</option>
                                <option value="in">Check In</option>
                                <option value="out">Check Out</option>
                            </select>
                        @endif
                        <span @class([
                            'app-badge-warning',
                            'self-start sm:self-auto' => $grandTotal < 1,
                        ])>
                            {{ $grandTotal }} item
                        </span>
                    </div>
                </div>

                @if($grandTotal < 1)
                    <div class="border-t border-slate-200 px-5 py-16">
                        <div class="mx-auto max-w-sm text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                <i class="fas fa-circle-check text-xl"></i>
                            </div>
                            <p class="mt-4 font-semibold text-slate-900">Tidak ada approval menunggu</p>
                            <p class="mt-1 text-sm leading-6 text-slate-500">Semua absensi sudah diproses atau disetujui otomatis.</p>
                            <a href="{{ route('admin.presence.history') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:text-emerald-900">
                                <i class="fas fa-clock-rotate-left text-xs"></i>
                                Lihat riwayat presensi
                            </a>
                        </div>
                    </div>
                @else
                    <div class="app-table-wrap rounded-none border-0 shadow-none">
                        <table class="data-table admin-table-xl">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Karyawan</th>
                                    <th>Tipe</th>
                                    <th>Foto</th>
                                    <th>Waktu & Lokasi</th>
                                    <th>Keterangan</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody x-ref="approvalBody">
                                @foreach($presences as $presence)
                                    @if($presence->is_approved === 'pending')
                                        <tr
                                            class="data-table-row"
                                            x-show="matches(@js(($presence->user->name ?? '').' '.$presence->date.' '.$presence->notes), 'in')"
                                            x-bind:data-visible="matches(@js(($presence->user->name ?? '').' '.$presence->date.' '.$presence->notes), 'in')"
                                        >
                                            <td class="text-slate-500">{{ $rowNumber++ }}</td>
                                            <td class="min-w-56">
                                                <div class="font-semibold text-slate-950">{{ $presence->user->name ?? '-' }}</div>
                                                <div class="mt-1 text-xs text-slate-500">{{ \Carbon\Carbon::parse($presence->date)->translatedFormat('l, d M Y') }}</div>
                                            </td>
                                            <td>
                                                <span class="app-badge bg-sky-100 text-sky-700">Check In</span>
                                            </td>
                                            <td>
                                                @if($presence->photo_in)
                                                    <x-lightbox-image 
                                                        src="{{ $presence->photo_in_url }}"
                                                        alt="Foto check in"
                                                        class="h-12 w-12 rounded-lg border border-slate-200 object-cover shadow-sm"
                                                    />
                                                @else
                                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                                        <i class="fas fa-camera"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="min-w-48">
                                                <div class="font-semibold text-slate-900">{{ $presence->check_in ? substr($presence->check_in, 0, 5) : '-' }}</div>
                                                @if($presence->lat_in && $presence->lng_in)
                                                    <a href="https://www.google.com/maps?q={{ $presence->lat_in }},{{ $presence->lng_in }}" target="_blank" class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-sky-700 hover:text-sky-900">
                                                        <i class="fas fa-location-dot"></i> Lihat Lokasi
                                                    </a>
                                                @else
                                                    <div class="mt-1 text-xs text-slate-400">Lokasi tidak tersedia</div>
                                                @endif
                                            </td>
                                            <td><div class="admin-table-text">{{ $presence->notes ?: '-' }}</div></td>
                                            <td>
                                                <div class="admin-table-actions">
                                                    <form action="{{ route('admin.presence.updateStatus', [$presence->id, 'approved']) }}?type=in" method="POST" data-confirm="Setujui check-in {{ $presence->user->name ?? 'ini' }}?" data-submit-lock>
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                                            <i class="fas fa-check"></i> Setuju
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.presence.updateStatus', [$presence->id, 'rejected']) }}?type=in" method="POST" data-confirm="Tolak check-in {{ $presence->user->name ?? 'ini' }}?" data-submit-lock>
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-rose-700">
                                                            <i class="fas fa-xmark"></i> Tolak
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif

                                    @if($presence->check_out && ($presence->is_approved_out ?? 'pending') === 'pending')
                                        <tr
                                            class="data-table-row"
                                            x-show="matches(@js(($presence->user->name ?? '').' '.$presence->date.' '.$presence->notes_out), 'out')"
                                            x-bind:data-visible="matches(@js(($presence->user->name ?? '').' '.$presence->date.' '.$presence->notes_out), 'out')"
                                        >
                                            <td class="text-slate-500">{{ $rowNumber++ }}</td>
                                            <td class="min-w-56">
                                                <div class="font-semibold text-slate-950">{{ $presence->user->name ?? '-' }}</div>
                                                <div class="mt-1 text-xs text-slate-500">{{ \Carbon\Carbon::parse($presence->date)->translatedFormat('l, d M Y') }}</div>
                                            </td>
                                            <td>
                                                <span class="app-badge bg-rose-100 text-rose-700">Check Out</span>
                                            </td>
                                            <td>
                                                @if($presence->photo_out)
                                                    <x-lightbox-image 
                                                        src="{{ $presence->photo_out_url }}"
                                                        alt="Foto check out"
                                                        class="h-12 w-12 rounded-lg border border-slate-200 object-cover shadow-sm"
                                                    />
                                                @else
                                                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                                        <i class="fas fa-camera"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="min-w-48">
                                                <div class="font-semibold text-slate-900">{{ $presence->check_out ? substr($presence->check_out, 0, 5) : '-' }}</div>
                                                @if($presence->lat_out && $presence->lng_out)
                                                    <a href="https://www.google.com/maps?q={{ $presence->lat_out }},{{ $presence->lng_out }}" target="_blank" class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-rose-700 hover:text-rose-900">
                                                        <i class="fas fa-location-dot"></i> Lihat Lokasi
                                                    </a>
                                                @else
                                                    <div class="mt-1 text-xs text-slate-400">Lokasi tidak tersedia</div>
                                                @endif
                                            </td>
                                            <td><div class="admin-table-text">{{ $presence->notes_out ?: '-' }}</div></td>
                                            <td>
                                                <div class="admin-table-actions">
                                                    <form action="{{ route('admin.presence.updateStatus', [$presence->id, 'approved']) }}" method="POST" data-confirm="Setujui check-out {{ $presence->user->name ?? 'ini' }}?" data-submit-lock>
                                                        @csrf
                                                        <input type="hidden" name="type" value="out">
                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                                            <i class="fas fa-check"></i> Setuju
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.presence.updateStatus', [$presence->id, 'rejected']) }}" method="POST" data-confirm="Tolak check-out {{ $presence->user->name ?? 'ini' }}?" data-submit-lock>
                                                        @csrf
                                                        <input type="hidden" name="type" value="out">
                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-rose-700">
                                                            <i class="fas fa-xmark"></i> Tolak
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                <tr x-show="!Array.from($refs.approvalBody.querySelectorAll('tr[data-visible]')).some(row => row.dataset.visible === 'true')" x-cloak>
                                    <td colspan="7" class="px-5 py-14 text-center text-slate-500">
                                        <p class="font-semibold">Tidak ada presensi yang cocok.</p>
                                        <p class="mt-1 text-sm text-slate-500">Ubah kata kunci atau filter status untuk melihat data lain.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
