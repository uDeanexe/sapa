<x-app-layout>
    <div
        class="admin-shell"
        x-data="{
            expandAll() {
                this.$root.querySelectorAll('details[data-presence-detail]').forEach(item => item.open = true);
            },
            collapseAll() {
                this.$root.querySelectorAll('details[data-presence-detail]').forEach(item => item.open = false);
            }
        }"
    >
        <div class="admin-container admin-container-fluid">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Riwayat Presensi</h2>
                        <p class="admin-subtitle">Lihat presensi karyawan berdasarkan periode dan nama.</p>
                    </div>

                    <a href="{{ route('admin.presence.index') }}" class="btn-secondary-soft">
                        <i class="fas fa-clipboard-check mr-2"></i> Approval
                    </a>
                </div>
            </div>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Disetujui</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $totalApproved }}</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Pending</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $totalPending }}</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Ditolak</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $totalRejected }}</p>
                </div>
            </section>

            <form method="GET" action="{{ route('admin.presence.history') }}" class="app-toolbar app-surface">
                <div>
                    <h3 class="app-section-title">Filter Riwayat</h3>
                    <p class="app-section-subtitle">{{ $months[$selectedMonth - 1] }} {{ $selectedYear }} � {{ $users->count() }} karyawan</p>
                </div>

                <div class="grid w-full grid-cols-1 gap-3 sm:w-auto sm:grid-cols-[150px_110px_minmax(220px,1fr)_auto_auto]">
                    <select name="month" class="app-input">
                        @foreach($months as $index => $month)
                            <option value="{{ $index + 1 }}" @selected($selectedMonth === $index + 1)>{{ $month }}</option>
                        @endforeach
                    </select>

                    <select name="year" class="app-input">
                        @for($year = now()->year; $year >= now()->year - 3; $year--)
                            <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                        @endfor
                    </select>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama karyawan..." class="app-input">

                    <button type="submit" class="btn-primary-soft">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>

                    @if(request('search') || request('month') || request('year'))
                        <a href="{{ route('admin.presence.history') }}" class="btn-secondary-soft">Reset</a>
                    @endif
                </div>
            </form>

            <section class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="app-section-title">Detail Presensi Karyawan</h3>
                        <p class="app-section-subtitle">Klik nama karyawan untuk melihat detail presensi per tanggal.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="expandAll()" class="btn-secondary-soft">Buka Semua</button>
                        <button type="button" @click="collapseAll()" class="btn-secondary-soft">Tutup Semua</button>
                    </div>
                </div>

                @forelse($users as $user)
                    @php
                        $records = $presenceData[$user->id] ?? collect();
                        $leaves = $leaveData[$user->id] ?? collect();
                        $approved = $records->where('is_approved', 'approved')->count();
                        $pending = $records->where('is_approved', 'pending')->count();
                        $rejected = $records->where('is_approved', 'rejected')->count();
                        $notCheckedOut = $records->where('is_approved', 'approved')->whereNull('check_out')->count();
                        $initials = collect(explode(' ', $user->name))->take(2)->map(fn ($word) => strtoupper(substr($word, 0, 1)))->join('');
                    @endphp

                    <details class="admin-card group" data-presence-detail>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 font-bold text-white">
                                    {{ $initials ?: '?' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-950">{{ $user->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $user->division->name ?? 'Tanpa Divisi' }} � {{ $records->count() }} presensi � {{ $leaves->count() }} izin/cuti</p>
                                </div>
                            </div>

                            <div class="hidden min-w-0 items-center justify-end gap-2 md:flex md:flex-nowrap">
                                <span class="app-badge-success">{{ $approved }} hadir</span>
                                @if($pending > 0)<span class="app-badge-warning">{{ $pending }} pending</span>@endif
                                @if($rejected > 0)<span class="app-badge bg-rose-100 text-rose-700">{{ $rejected }} ditolak</span>@endif
                                @if($notCheckedOut > 0)<span class="app-badge bg-sky-100 text-sky-700">{{ $notCheckedOut }} belum out</span>@endif
                            </div>

                            <i class="fas fa-chevron-down text-slate-400 transition group-open:rotate-180"></i>
                        </summary>

                        <div class="border-t border-slate-100 p-5">
                            @if($leaves->isNotEmpty())
                                <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 p-4">
                                    <p class="text-sm font-semibold text-sky-900">Izin/Cuti Disetujui</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach($leaves as $leave)
                                            <span class="app-badge bg-sky-100 text-sky-700">
                                                {{ ucfirst($leave->type) }}:
                                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }}
                                                -
                                                {{ \Carbon\Carbon::parse($leave->end_date)->format('d M') }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="app-table-wrap rounded-none border-0 shadow-none">
                                <table class="data-table admin-table-xl w-full">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Masuk</th>
                                            <th>Foto In</th>
                                            <th>Pulang</th>
                                            <th>Foto Out</th>
                                            <th>Durasi</th>
                                            <th>Ket. In</th>
                                            <th>Ket. Out</th>
                                            <th>Status In</th>
                                            <th>Status Out</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($records as $record)
                                            @php
                                                $date = \Carbon\Carbon::parse($record->date);
                                                $duration = null;
                                                if ($record->check_in && $record->check_out) {
                                                    $in = \Carbon\Carbon::createFromTimeString($record->check_in);
                                                    $out = \Carbon\Carbon::createFromTimeString($record->check_out); // Diasumsikan check-in dan out di hari yang sama
                                                    $minutes = $in->diffInMinutes($out);
                                                    $duration = floor($minutes / 60).'j '.($minutes % 60).'m';
                                                }

                                                $statusLabels = [
                                                    'approved' => 'Disetujui',
                                                    'pending' => 'Pending',
                                                    'rejected' => 'Ditolak',
                                                    'not_out' => 'Belum out',
                                                ];

                                                $statusIn = $record->is_approved ?? 'pending';
                                                $statusInClass = $statusIn === 'approved' ? 'app-badge-success' : ($statusIn === 'rejected' ? 'app-badge bg-rose-100 text-rose-700' : 'app-badge-warning');
                                                $statusOut = $record->check_out ? ($record->is_approved_out ?? 'pending') : 'not_out';
                                                $statusOutClass = $statusOut === 'approved' ? 'app-badge-success' : ($statusOut === 'rejected' ? 'app-badge bg-rose-100 text-rose-700' : ($statusOut === 'not_out' ? 'app-badge-muted' : 'app-badge-warning'));
                                            @endphp
                                            <tr class="data-table-row">
                                                <td class="min-w-40">
                                                    <div class="font-semibold text-slate-950">{{ $date->format('d M Y') }}</div>
                                                    <div class="mt-1 text-xs text-slate-500">{{ $date->translatedFormat('l') }}</div>
                                                </td>
                                                <td>{{ $record->check_in ? substr($record->check_in, 0, 5) : '-' }}</td>
                                                <td>
                                                    @if($record->photo_in)
                                                        <x-lightbox-image 
                                                            src="{{ $record->photo_in_url }}"
                                                            alt="Foto masuk"
                                                            class="h-10 w-10 rounded-lg border border-slate-200 object-cover shadow-sm"
                                                        />
                                                    @else
                                                        <span class="text-slate-400">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $record->check_out ? substr($record->check_out, 0, 5) : '-' }}</td>
                                                <td>
                                                    @if($record->photo_out)
                                                        <x-lightbox-image 
                                                            src="{{ $record->photo_out_url }}"
                                                            alt="Foto pulang"
                                                            class="h-10 w-10 rounded-lg border border-slate-200 object-cover shadow-sm"
                                                        />
                                                    @else
                                                        <span class="text-slate-400">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $duration ?: '-' }}</td>
                                                <td><div class="admin-table-text">{{ $record->notes ?: '-' }}</div></td>
                                                <td><div class="admin-table-text">{{ $record->notes_out ?: '-' }}</div></td>
                                                <td><span class="{{ $statusInClass }}">{{ $statusLabels[$statusIn] ?? ucfirst($statusIn) }}</span></td>
                                                <td><span class="{{ $statusOutClass }}">{{ $statusLabels[$statusOut] ?? ucfirst($statusOut) }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="px-5 py-10 text-center text-slate-500">Tidak ada data presensi pada periode ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </details>
                @empty
                    <div class="app-surface px-5 py-14 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <p class="mt-3 font-semibold text-slate-900">Karyawan tidak ditemukan</p>
                        <p class="mt-1 text-sm text-slate-500">Coba ubah filter pencarian.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
