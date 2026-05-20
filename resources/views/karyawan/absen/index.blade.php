<x-app-layout>
    <div class="admin-shell attendance-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Riwayat Absensi</h2>
                        <p class="admin-subtitle">Pantau kehadiran, status approval admin, foto, dan lokasi absensi.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('karyawan.attendance.request-permit') }}" class="btn-secondary-soft">
                            <i class="fas fa-file-signature mr-2"></i> Izin/Cuti
                        </a>
                        <a href="{{ route('karyawan.attendance.checkin') }}" class="btn-primary-soft">
                            <i class="fas fa-circle-check mr-2"></i> Check In
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert-error">{{ session('error') }}</div>
            @endif

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="metric-card metric-emerald">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="metric-label">Hadir</p>
                            <p class="metric-value">{{ $stats['present'] ?? 0 }}</p>
                            <p class="metric-note">hari bulan ini</p>
                        </div>
                        <span class="metric-icon"><i class="fas fa-user-check"></i></span>
                    </div>
                </div>
                <div class="metric-card metric-amber">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="metric-label">Izin/Cuti</p>
                            <p class="metric-value">{{ $stats['permit'] ?? 0 }}</p>
                            <p class="metric-note">pengajuan bulan ini</p>
                        </div>
                        <span class="metric-icon"><i class="fas fa-file-lines"></i></span>
                    </div>
                </div>
                <div class="metric-card metric-rose">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="metric-label">Absen</p>
                            <p class="metric-value">{{ $stats['absent'] ?? 0 }}</p>
                            <p class="metric-note">hari tanpa catatan</p>
                        </div>
                        <span class="metric-icon"><i class="fas fa-calendar-xmark"></i></span>
                    </div>
                </div>
                <div class="metric-card metric-sky">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="metric-label">Terlambat</p>
                            <p class="metric-value">{{ $stats['late'] ?? 0 }}</p>
                            <p class="metric-note">kali bulan ini</p>
                        </div>
                        <span class="metric-icon"><i class="fas fa-clock"></i></span>
                    </div>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="admin-card-title">Data Absensi Bulan Ini</h3>
                        <p class="app-section-subtitle">Foto dan lokasi tampil sebagai bukti yang dapat direview admin.</p>
                    </div>
                </div>

                <div class="app-table-wrap">
                    <table class="data-table admin-table-xl">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Masuk</th>
                                <th>Pulang</th>
                                <th>Approval</th>
                                <th>Lokasi</th>
                                <th>Foto</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances ?? [] as $attendance)
                                @php
                                    $checkIn = $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : null;
                                    $checkOut = $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : null;
                                    $approval = $attendance->is_approved ?? 'pending';
                                    $approvalOut = $attendance->is_approved_out ?? null;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $attendance->date->format('d M Y') }}</div>
                                        <div class="text-xs text-slate-500">{{ $attendance->date->translatedFormat('l') }}</div>
                                    </td>
                                    <td>
                                        <span class="font-semibold text-slate-900">{{ $checkIn ?? '-' }}</span>
                                        @if($attendance->check_in && \Carbon\Carbon::parse($attendance->check_in)->format('H:i:s') > '08:00:00')
                                            <span class="ml-1 text-xs font-semibold text-amber-600">Terlambat</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-semibold text-slate-900">{{ $checkOut ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="flex flex-col gap-1">
                                            <span class="attendance-status-badge status-{{ $approval }}">Masuk: {{ ucfirst($approval) }}</span>
                                            @if($checkOut)
                                                <span class="attendance-status-badge status-{{ $approvalOut ?? 'pending' }}">Pulang: {{ ucfirst($approvalOut ?? 'pending') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="space-y-1 text-xs text-slate-600">
                                            <p><span class="font-semibold">In:</span> {{ $attendance->lat_in ? number_format($attendance->lat_in, 5).', '.number_format($attendance->lng_in, 5) : '-' }}</p>
                                            <p><span class="font-semibold">Out:</span> {{ $attendance->lat_out ? number_format((float) $attendance->lat_out, 5).', '.number_format((float) $attendance->lng_out, 5) : '-' }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            @if($attendance->photo_in_url)
                                                <a href="{{ $attendance->photo_in_url }}" target="_blank" class="attendance-photo-link">In</a>
                                            @endif
                                            @if($attendance->photo_out_url)
                                                <a href="{{ $attendance->photo_out_url }}" target="_blank" class="attendance-photo-link">Out</a>
                                            @endif
                                            @if(! $attendance->photo_in_url && ! $attendance->photo_out_url)
                                                <span class="text-slate-500">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="admin-table-text text-xs">
                                            {{ Str::limit($attendance->notes ?: $attendance->notes_out ?: '-', 70) }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="app-empty-state">
                                            <span class="app-empty-state-icon"><i class="fas fa-inbox"></i></span>
                                            <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada data absensi</p>
                                            <p class="mt-1 text-xs text-slate-500">Data check in dan check out akan muncul di sini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div>
                {{ $attendances->links() ?? '' }}
            </div>
        </div>
    </div>
</x-app-layout>
