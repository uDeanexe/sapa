<x-app-layout>
    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">{{ __('Profil Saya') }}</h2>
                        <p class="admin-subtitle">{{ __('Kelola informasi dan pengaturan akun Anda') }}</p>
                    </div>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Profile Information -->
                <div class="admin-card">
                    <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Informasi Profil') }}</h2>
                    </div>
                    <form class="p-6 space-y-6">
                        <div class="flex items-end gap-4">
                            <div>
                                <img src="{{ Auth::user()->avatar ?? 'https://via.placeholder.com/100' }}" alt="{{ Auth::user()->name }}" class="h-20 w-20 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                            </div>
                            <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-900 hover:bg-slate-100 transition dark:border-slate-700 dark:text-slate-100 dark:hover:bg-white/10">
                                {{ __('Ubah Foto') }}
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-900 mb-2 dark:text-slate-200">{{ __('Nama Lengkap') }}</label>
                                <input type="text" value="{{ Auth::user()->name }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-900 mb-2 dark:text-slate-200">{{ __('Email') }}</label>
                                <input type="email" value="{{ Auth::user()->email }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-900 mb-2 dark:text-slate-200">{{ __('Divisi') }}</label>
                                <input type="text" value="{{ Auth::user()->division->name ?? '-' }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-900 mb-2 dark:text-slate-200">{{ __('Status') }}</label>
                                <input type="text" value="{{ ucfirst(Auth::user()->status ?? 'active') }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-900 mb-2 dark:text-slate-200">{{ __('Nomor Telepon') }}</label>
                                <input type="text" value="{{ Auth::user()->phone ?? '-' }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-900 mb-2 dark:text-slate-200">{{ __('Tanggal Bergabung') }}</label>
                                <input type="text" value="{{ Auth::user()->created_at->format('d M Y') }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-900 mb-2 dark:text-slate-200">{{ __('Alamat') }}</label>
                            <textarea readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600 h-20">{{ Auth::user()->address ?? '-' }}</textarea>
                        </div>
                    </form>
                </div>

                <!-- Password -->
                <div class="admin-card">
                    <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Ubah Password') }}</h2>
                    </div>
                    <form action="{{ route('password.update') }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="current_password" class="block text-sm font-medium text-slate-900 mb-2 dark:text-slate-200">{{ __('Password Saat Ini') }}</label>
                            <input type="password" id="current_password" name="current_password" class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-600" required>
                            @error('current_password')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-900 mb-2 dark:text-slate-200">{{ __('Password Baru') }}</label>
                            <input type="password" id="password" name="password" class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-600" required>
                            @error('password')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-900 mb-2 dark:text-slate-200">{{ __('Konfirmasi Password') }}</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-600" required>
                        </div>

                        <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-white font-medium hover:bg-sky-700 transition">
                            {{ __('Ubah Password') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Stats -->
                <div class="admin-card p-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4 dark:text-slate-100">{{ __('Statistik Kerja') }}</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-800">
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Tugas Selesai') }}</p>
                            <p class="text-lg font-bold text-emerald-600">{{ $stats['completed_tasks'] ?? 0 }}</p>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-800">
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Tugas Aktif') }}</p>
                            <p class="text-lg font-bold text-sky-600">{{ $stats['active_tasks'] ?? 0 }}</p>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Absensi Bulan Ini') }}</p>
                            <p class="text-lg font-bold text-amber-600">{{ $stats['monthly_attendance'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="admin-card p-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4 dark:text-slate-100">{{ __('Preferensi') }}</h3>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" checked class="rounded border-slate-300 text-sky-600 dark:border-slate-700">
                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-200">{{ __('Notifikasi Chat') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" checked class="rounded border-slate-300 text-sky-600 dark:border-slate-700">
                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-200">{{ __('Notifikasi Email') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-slate-300 text-sky-600 dark:border-slate-700">
                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-200">{{ __('Notifikasi Agenda') }}</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" checked class="rounded border-slate-300 text-sky-600 dark:border-slate-700">
                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-200">{{ __('Tampilkan Status Online') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Security -->
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-300/25 dark:bg-amber-500/10">
                    <p class="text-sm font-semibold text-amber-900 mb-2 dark:text-amber-200">🔒 {{ __('Keamanan') }}</p>
                    <ul class="space-y-1 text-xs text-amber-800 dark:text-amber-200/90">
                        <li>✓ {{ __('Two-factor authentication') }} (optional)</li>
                        <li>✓ {{ __('Perangkat aktif') }}: 1</li>
                        <li>• {{ __('Terakhir login') }}: {{ Auth::user()->last_login_at?->diffForHumans() ?? 'Belum login' }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
