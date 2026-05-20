<x-app-layout>
    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">{{ __('Dashboard Karyawan') }}</h2>
                        <p class="admin-subtitle">{{ now()->translatedFormat('l, d F Y') }}.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-slate-900">{{ now()->format('H:i') }}</p>
                        <p class="text-sm text-slate-500">Waktu Kerja</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Status Kehadiran -->
            <div class="admin-card p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">{{ __('Status Hari Ini') }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">
                            @if(isset($todayPresence) && $todayPresence)
                                <span class="text-emerald-600"><i class="fas fa-check mr-1"></i> Hadir</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </span>
                </div>
            </div>

            <!-- Tugas Hari Ini -->
            <div class="admin-card p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">{{ __('Tugas Hari Ini') }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $todayTasks ?? 0 }}</p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                        <i class="fas fa-tasks text-xl"></i>
                    </span>
                </div>
            </div>

            <!-- Chat Baru -->
            <div class="admin-card p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">{{ __('Pesan Baru') }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $newMessages ?? 0 }}</p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <i class="fas fa-envelope text-xl"></i>
                    </span>
                </div>
            </div>

            <!-- Notifikasi -->
            <div class="admin-card p-6 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">{{ __('Pemberitahuan') }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $notificationsCount ?? 0 }}</p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                        <i class="fas fa-bell text-xl"></i>
                    </span>
                </div>
            </div>
        </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Left Column - Tasks & Activities -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Tugas Saya -->
                <div class="admin-card">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-900">{{ __('Tugas Saya') }}</h2>
                            <a href="{{ route('technician.dashboard') }}" class="text-sm font-medium text-sky-600 hover:text-sky-700">{{ __('Lihat di Tracker') }}</a>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @forelse($tasks ?? [] as $task)
                            <div class="flex items-start gap-4 px-6 py-4 hover:bg-slate-50 transition">
                                <div class="mt-1 flex h-5 w-5 items-center justify-center rounded border border-slate-300 text-xs">
                                    @if($task->status === 'completed')
                                        <i class="fas fa-check text-emerald-600"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-900">{{ $task->title }}</p>
                                    <p class="mt-1 text-sm text-slate-600">{{ Str::limit($task->description, 100) }}</p>
                                    <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ $task->due_date->format('d M Y') }}</span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="{
                                        'bg-emerald-50 text-emerald-700': '{{ $task->status }}' === 'completed',
                                        'bg-amber-50 text-amber-700': '{{ $task->status }}' === 'in_progress',
                                        'bg-slate-50 text-slate-700': '{{ $task->status }}' === 'pending'
                                    }">
                                    {{ ucfirst(str_replace('_', ' ', $task->status ?? 'pending')) }}
                                </span>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center">
                                <i class="fas fa-inbox text-3xl text-slate-300 mb-2 block"></i>
                                <p class="text-slate-600">{{ __('Tidak ada tugas hari ini') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Aktivitas Terbaru') }}</h2>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @forelse($activities ?? [] as $activity)
                            <div class="flex gap-4 px-6 py-4 hover:bg-slate-50 transition">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-slate-100">
                                    <i class="fas fa-{{ $activity->icon ?? 'circle' }} text-slate-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900">{{ $activity->description }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center">
                                <p class="text-slate-600">{{ __('Tidak ada aktivitas terbaru') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="space-y-8">
                <!-- Absensi Quick View -->
                <div class="admin-card">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Absensi Bulan Ini') }}</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-2xl font-bold text-emerald-600">{{ $attendanceStats['present'] ?? 0 }}</p>
                                <p class="text-xs text-slate-600">{{ __('Hadir') }}</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-amber-600">{{ $attendanceStats['permit'] ?? 0 }}</p>
                                <p class="text-xs text-slate-600">{{ __('Izin') }}</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-rose-600">{{ $attendanceStats['absent'] ?? 0 }}</p>
                                <p class="text-xs text-slate-600">{{ __('Absen') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('karyawan.attendance.index') }}" class="mt-4 block w-full rounded-lg bg-slate-100 px-4 py-2 text-center text-sm font-medium text-slate-900 hover:bg-slate-200 transition">
                            {{ __('Detail Absensi') }}
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="admin-card">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Akses Cepat') }}</h2>
                    </div>
                    <div class="divide-y divide-slate-200">
                        <a href="{{ route('karyawan.attendance.checkin') }}" class="flex items-center gap-3 px-6 py-3 hover:bg-slate-50 transition">
                            <i class="fas fa-fingerprint text-sky-600"></i>
                            <span class="text-sm font-medium text-slate-900">{{ __('Check In Sekarang') }}</span>
                            <i class="fas fa-chevron-right ml-auto text-slate-400"></i>
                        </a>
                        <a href="{{ route('karyawan.chat.index') }}" class="flex items-center gap-3 px-6 py-3 hover:bg-slate-50 transition">
                            <i class="fas fa-comments text-amber-600"></i>
                            <span class="text-sm font-medium text-slate-900">{{ __('Chat & Pesan') }}</span>
                            <i class="fas fa-chevron-right ml-auto text-slate-400"></i>
                        </a>
                        <a href="{{ route('karyawan.agenda.index') }}" class="flex items-center gap-3 px-6 py-3 hover:bg-slate-50 transition">
                            <i class="fas fa-calendar-alt text-emerald-600"></i>
                            <span class="text-sm font-medium text-slate-900">{{ __('Agenda & Event') }}</span>
                            <i class="fas fa-chevron-right ml-auto text-slate-400"></i>
                        </a>
                    </div>
                </div>

                <!-- Berita Terbaru -->
                <div class="admin-card">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Berita & Pengumuman') }}</h2>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @forelse($news ?? [] as $item)
                            <div class="px-6 py-4 hover:bg-slate-50 transition">
                                <p class="text-sm font-medium text-slate-900">{{ $item->data['title'] ?? $item->title ?? 'Notifikasi' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $item->created_at->format('d M Y') }}</p>
                            </div>
                        @empty
                            <div class="px-6 py-4 text-center">
                                <p class="text-sm text-slate-600">{{ __('Tidak ada berita terbaru') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
