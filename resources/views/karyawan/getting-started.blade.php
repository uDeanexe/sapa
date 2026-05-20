<x-app-layout>
    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">{{ __('Selamat Datang') }}, {{ Auth::user()->name }}</h2>
                        <p class="admin-subtitle">{{ __('Akses cepat untuk fitur karyawan.') }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-12">
                <a href="{{ route('karyawan.dashboard') }}" class="group admin-card p-6 hover:shadow-lg hover:border-sky-300 transition">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-sky-100 text-sky-600 group-hover:bg-sky-200 transition">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-sky-600 transition">{{ __('Dashboard Saya') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Ringkasan kerja harian, tugas, dan notifikasi penting.') }}</p>
                    <div class="mt-4 text-sky-600 font-medium text-sm group-hover:translate-x-1 transition">{{ __('Buka →') }}</div>
                </a>

                <a href="{{ route('karyawan.attendance.checkin') }}" class="group admin-card p-6 border-emerald-200 bg-emerald-50 hover:shadow-lg transition border-2">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <i class="fas fa-fingerprint text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Check In/Out') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Absen masuk dan pulang dengan foto & lokasi.') }}</p>
                    <div class="mt-4 text-emerald-600 font-medium text-sm group-hover:translate-x-1 transition">{{ __('Mulai →') }}</div>
                </a>

                <a href="{{ route('karyawan.chat.index') }}" class="group admin-card p-6 border-amber-200 bg-amber-50 hover:shadow-lg transition border-2">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <i class="fas fa-comments text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Chat & Pesan') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Koordinasi tim lewat chat real-time.') }}</p>
                    <div class="mt-4 text-amber-600 font-medium text-sm group-hover:translate-x-1 transition">{{ __('Buka →') }}</div>
                </a>

                <a href="{{ route('karyawan.attendance.index') }}" class="group admin-card p-6 hover:shadow-lg hover:border-emerald-300 transition">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 group-hover:bg-emerald-200 transition">
                        <i class="fas fa-calendar-check text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-emerald-600 transition">{{ __('Riwayat Absensi') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Pantau status approval, foto, dan lokasi.') }}</p>
                    <div class="mt-4 text-emerald-600 font-medium text-sm group-hover:translate-x-1 transition">{{ __('Lihat →') }}</div>
                </a>

                <a href="{{ route('karyawan.attendance.request-permit') }}" class="group admin-card p-6 hover:shadow-lg hover:border-purple-300 transition">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 text-purple-600 group-hover:bg-purple-200 transition">
                        <i class="fas fa-file-signature text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-purple-600 transition">{{ __('Ajukan Izin/Cuti') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Kirim permohonan izin/cuti/sakit beserta lampiran.') }}</p>
                    <div class="mt-4 text-purple-600 font-medium text-sm group-hover:translate-x-1 transition">{{ __('Ajukan →') }}</div>
                </a>

                <a href="{{ route('karyawan.notifications') }}" class="group admin-card p-6 hover:shadow-lg hover:border-rose-300 transition">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-rose-100 text-rose-600 group-hover:bg-rose-200 transition">
                        <i class="fas fa-bell text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-rose-600 transition">{{ __('Notifikasi') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Pengumuman dan informasi penting dari admin.') }}</p>
                    <div class="mt-4 text-rose-600 font-medium text-sm group-hover:translate-x-1 transition">{{ __('Buka →') }}</div>
                </a>

                <a href="{{ route('karyawan.agenda.index') }}" class="group admin-card p-6 hover:shadow-lg hover:border-indigo-300 transition">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-200 transition">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-indigo-600 transition">{{ __('Agenda') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Jadwal dan event penting bulan ini.') }}</p>
                    <div class="mt-4 text-indigo-600 font-medium text-sm group-hover:translate-x-1 transition">{{ __('Lihat →') }}</div>
                </a>

                <a href="{{ route('karyawan.profile') }}" class="group admin-card p-6 hover:shadow-lg hover:border-slate-300 transition">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-slate-200 transition">
                        <i class="fas fa-user text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-slate-700 transition">{{ __('Profil') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Informasi akun, password, dan preferensi.') }}</p>
                    <div class="mt-4 text-slate-700 font-medium text-sm group-hover:translate-x-1 transition">{{ __('Buka →') }}</div>
                </a>
            </div>

            <div class="mb-12">
                <div class="admin-card border-emerald-200 bg-emerald-50 p-8">
                    <h2 class="text-2xl font-bold text-emerald-900 mb-4"><i class="fas fa-lightbulb mr-2"></i>{{ __('Tips & Trik') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-200 text-emerald-900 font-bold">1</div>
                            <div>
                                <p class="font-semibold text-emerald-900">{{ __('Check-in Tepat Waktu') }}</p>
                                <p class="text-sm text-emerald-800">{{ __('Pastikan lokasi aktif dan foto jelas sebelum submit.') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-200 text-emerald-900 font-bold">2</div>
                            <div>
                                <p class="font-semibold text-emerald-900">{{ __('Ajukan Izin Lebih Awal') }}</p>
                                <p class="text-sm text-emerald-800">{{ __('Lampiran membantu proses approval admin lebih cepat.') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-200 text-emerald-900 font-bold">3</div>
                            <div>
                                <p class="font-semibold text-emerald-900">{{ __('Pantau Notifikasi') }}</p>
                                <p class="text-sm text-emerald-800">{{ __('Cek notifikasi untuk update penting & status izin.') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-200 text-emerald-900 font-bold">4</div>
                            <div>
                                <p class="font-semibold text-emerald-900">{{ __('Gunakan Chat untuk Koordinasi') }}</p>
                                <p class="text-sm text-emerald-800">{{ __('Gunakan chat group untuk komunikasi cepat dengan tim.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="admin-card p-8">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6"><i class="fas fa-bolt mr-2"></i>{{ __('Akses Cepat') }}</h2>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('jobs.create') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition">
                            <i class="fas fa-plus-circle"></i> {{ __('Buat Tugas') }}
                        </a>
                        <a href="{{ route('technician.dashboard') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition">
                            <i class="fas fa-stopwatch"></i> {{ __('Tracker Kerja') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
