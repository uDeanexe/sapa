@php
    $role = Auth::user()->role ?? 'user';
    $isKepala = in_array($role, ['kepala', 'admin'], true);
    $isKaryawan = $role === 'karyawan';
@endphp

<aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white shadow-sm md:block sticky top-0 h-screen dark:border-slate-700 dark:bg-slate-900">
    <div class="flex h-full flex-col p-6">
        <div class="mb-8 flex shrink-0 items-center pl-2">
            <a href="{{ $isKaryawan ? route('karyawan.dashboard') : route('dashboard') }}">
                <x-application-logo class="block h-10 w-auto fill-current text-slate-800" />
            </a>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto pr-1 overscroll-contain">
            @if($isKaryawan)
                <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Karyawan</p>

                <x-nav-link :href="route('karyawan.dashboard')" :active="request()->routeIs('karyawan.dashboard')"
                    class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium">
                    <i class="fas fa-grid-2 mr-3 w-5"></i> {{ __('Dashboard') }}
                </x-nav-link>

                <x-nav-link :href="route('karyawan.attendance.checkin')" :active="request()->routeIs('karyawan.attendance.*')"
                    class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium">
                    <i class="fas fa-fingerprint mr-3 w-5"></i> {{ __('Absensi') }}
                </x-nav-link>

                <x-nav-link :href="route('karyawan.agenda.index')" :active="request()->routeIs('karyawan.agenda.*')"
                    class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium">
                    <i class="fas fa-calendar-alt mr-3 w-5"></i> {{ __('Agenda') }}
                </x-nav-link>

                <x-nav-link :href="route('karyawan.chat.index')" :active="request()->routeIs('karyawan.chat.*')"
                    class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium">
                    <i class="fas fa-comments mr-3 w-5"></i> {{ __('Chat Group') }}
                </x-nav-link>

                <x-nav-link :href="route('karyawan.profile')" :active="request()->routeIs('karyawan.profile')"
                    class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium">
                    <i class="fas fa-user mr-3 w-5"></i> {{ __('Profil') }}
                </x-nav-link>

                <p class="px-3 pb-2 pt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pekerjaan</p>

                <x-nav-link :href="route('technician.dashboard')" :active="request()->routeIs('technician.dashboard')"
                    class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium">
                    <i class="fas fa-stopwatch mr-3 w-5"></i> {{ __('Tracker Kerja') }}
                </x-nav-link>

                {{-- <x-nav-link :href="route('jobs.create')" :active="request()->routeIs('jobs.create')"
                    class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium">
                    <i class="fas fa-plus-circle mr-3 w-5"></i> {{ __('Buat Tugas Baru') }}
                </x-nav-link> --}}
            @else
                <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Utama</p>

                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                    class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium transition-colors duration-200">
                    <i class="fas fa-home mr-3 w-5"></i> {{ __('Dashboard') }}
                </x-nav-link>

                @if($isKepala)
                    <p class="px-3 pb-2 pt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400">Manajemen</p>

                    <x-nav-link :href="route('divisions.index')" :active="request()->routeIs('divisions.*')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-layer-group mr-3 w-5"></i> {{ __('Pengaturan Divisi') }}
                    </x-nav-link>

                    <x-nav-link :href="route('users-management.index')" :active="request()->routeIs('users-management.*')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-users mr-3 w-5"></i> {{ __('Manajemen Karyawan') }}
                    </x-nav-link>

                    <div class="space-y-1">
                        <button type="button" @click="openKehadiran = !openKehadiran"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition {{ (request()->routeIs('admin.presence.*') || request()->routeIs('admin.perizinan')) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5' }}">
                            <span class="flex items-center">
                                <i class="fas fa-calendar-check mr-3 w-5 {{ (request()->routeIs('admin.presence.*') || request()->routeIs('admin.perizinan')) ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                {{ __('Kehadiran') }}
                            </span>
                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': openKehadiran }"></i>
                        </button>

                        <div x-show="openKehadiran" x-cloak x-transition class="ml-4 space-y-1 border-l border-slate-200 pl-4 dark:border-slate-700">
                            <x-nav-link :href="route('admin.presence.index')" :active="request()->routeIs('admin.presence.index')" class="block w-full justify-start border-none py-2 text-xs">
                                Approval Absensi
                            </x-nav-link>
                            <x-nav-link :href="route('admin.perizinan')" :active="request()->routeIs('admin.perizinan')" class="block w-full justify-start border-none py-2 text-xs">
                                Izin & Cuti
                            </x-nav-link>
                            <x-nav-link :href="route('admin.presence.schedule')" :active="request()->routeIs('admin.presence.schedule')" class="block w-full justify-start border-none py-2 text-xs">
                                Jadwal Kerja
                            </x-nav-link>
                            <x-nav-link :href="route('admin.presence.history')" :active="request()->routeIs('admin.presence.history')" class="block w-full justify-start border-none py-2 text-xs">
                                Riwayat Presensi
                            </x-nav-link>
                            <x-nav-link :href="route('admin.presence.settings')" :active="request()->routeIs('admin.presence.settings')" class="block w-full justify-start border-none py-2 text-xs">
                                Settings Absensi
                            </x-nav-link>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <button type="button" @click="openPekerjaan = !openPekerjaan"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition {{ (request()->routeIs('jobs.*') || request()->routeIs('admin.clients.*')) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5' }}">
                            <span class="flex items-center">
                                <i class="fas fa-briefcase mr-3 w-5 {{ (request()->routeIs('jobs.*') || request()->routeIs('admin.clients.*')) ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                                {{ __('Pekerjaan') }}
                            </span>
                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': openPekerjaan }"></i>
                        </button>

                        <div x-show="openPekerjaan" x-cloak x-transition class="ml-4 space-y-1 border-l border-slate-200 pl-4 dark:border-slate-700">
                            <x-nav-link :href="route('jobs.index')" :active="request()->routeIs('jobs.index')" class="block w-full justify-start border-none py-2 text-xs">
                                Daftar Tugas
                            </x-nav-link>
                            <x-nav-link :href="route('jobs.create')" :active="request()->routeIs('jobs.create')" class="block w-full justify-start border-none py-2 text-xs">
                                Buat Tugas Baru
                            </x-nav-link>
                            <x-nav-link :href="route('admin.clients.index')" :active="request()->routeIs('admin.clients.*')" class="block w-full justify-start border-none py-2 text-xs">
                                Data Client
                            </x-nav-link>
                        </div>
                    </div>

                    @if(Route::has('admin.checklist'))
                        <x-nav-link :href="route('admin.checklist')" :active="request()->routeIs('admin.checklist')"
                            class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium transition-colors duration-200">
                            <i class="fas fa-clipboard-check mr-3 w-5"></i> {{ __('Checklist') }}
                        </x-nav-link>
                    @endif

                    <div class="space-y-1">
                        <button type="button" @click="openRekrutmen = !openRekrutmen"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('recruitment.*') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5' }}">
                            <span class="flex items-center">
                                <i class="fas fa-user-tie mr-3 w-5 {{ request()->routeIs('recruitment.*') ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-400 dark:text-slate-400' }}"></i>
                                {{ __('Rekrutmen') }}
                            </span>
                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': openRekrutmen }"></i>
                        </button>
                        <div x-show="openRekrutmen" x-cloak x-transition class="ml-4 space-y-1 border-l border-slate-200 pl-4 dark:border-slate-700">
                            <x-nav-link :href="route('recruitment.profil')" :active="request()->routeIs('recruitment.profil')" class="block w-full justify-start border-none py-2 text-xs rounded-lg px-3 text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5">
                                Profil
                            </x-nav-link>
                            <x-nav-link :href="route('recruitment.index')" :active="request()->routeIs('recruitment.index')" class="block w-full justify-start border-none py-2 text-xs rounded-lg px-3 text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5">
                                Manajemen Rekrutmen
                            </x-nav-link>
                            <x-nav-link :href="route('recruitment.lowongan')" :active="request()->routeIs('recruitment.lowongan')" class="block w-full justify-start border-none py-2 text-xs rounded-lg px-3 text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5">
                                Lowongan Pekerjaan
                            </x-nav-link>
                            <x-nav-link :href="route('recruitment.kandidat')" :active="request()->routeIs('recruitment.kandidat')" class="block w-full justify-start border-none py-2 text-xs rounded-lg px-3 text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5">
                                Kandidat
                            </x-nav-link>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <button type="button" @click="openKpi = !openKpi"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('kpi.*') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5' }}">
                            <span class="flex items-center">
                                <i class="fas fa-chart-line mr-3 w-5 {{ request()->routeIs('kpi.*') ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-400 dark:text-slate-400' }}"></i>
                                {{ __('KPI') }}
                            </span>
                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': openKpi }"></i>
                        </button>
                        <div x-show="openKpi" x-cloak x-transition class="ml-4 space-y-1 border-l border-slate-200 pl-4 dark:border-slate-700">
                            <x-nav-link :href="route('kpi.formulir')" :active="request()->routeIs('kpi.formulir')" class="block w-full justify-start border-none py-2 text-xs rounded-lg px-3 text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5">
                                Formulir
                            </x-nav-link>
                            <x-nav-link :href="route('kpi.jadwal')" :active="request()->routeIs('kpi.jadwal')" class="block w-full justify-start border-none py-2 text-xs rounded-lg px-3 text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5">
                                Jadwal
                            </x-nav-link>
                            <x-nav-link :href="route('kpi.evaluasi')" :active="request()->routeIs('kpi.evaluasi')" class="block w-full justify-start border-none py-2 text-xs rounded-lg px-3 text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5">
                                Evaluasi
                            </x-nav-link>
                        </div>
                    </div>

                    <x-nav-link :href="route('admin.messages')" :active="request()->routeIs('admin.messages')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-comments mr-3 w-5"></i> {{ __('Chat Group') }}
                    </x-nav-link>
                @endif
            @endif
        </nav>

        <div class="mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
            <div class="mb-4 px-3">
                <p class="text-xs font-bold uppercase tracking-wide text-emerald-600">{{ $role }}</p>
                <p class="w-40 truncate text-sm font-medium text-slate-700 dark:text-slate-100">{{ Auth::user()->name }}</p>
            </div>
            <div class="px-3 pb-3 flex items-center gap-2">
                <button
                    type="button"
                    data-theme-toggle
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    aria-label="Ganti tema"
                    title="Dark/Light"
                >
                    <i class="fas fa-moon text-[13px]" data-theme-icon aria-hidden="true"></i>
                </button>

                <form method="POST" action="{{ route('logout') }}" class="flex-1" data-logout-confirm>
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-500/10">
                        <i class="fas fa-sign-out-alt mr-2"></i> {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

<div x-cloak x-show="openMobile" class="md:hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-slate-950/50" @click="openMobile = false"></div>
    <aside class="absolute left-0 top-0 h-full w-72 bg-white shadow-2xl border-r border-slate-200 dark:bg-slate-900 dark:border-slate-700">
        <div class="flex h-full flex-col p-5">
            <div class="flex items-center justify-between">
                <a href="{{ $isKaryawan ? route('karyawan.dashboard') : route('dashboard') }}" class="inline-flex items-center gap-2">
                    <x-application-logo class="h-9 w-auto fill-current text-slate-800" />
                </a>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100" @click="openMobile = false" aria-label="Tutup menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="mt-6 flex-1 space-y-1 overflow-y-auto pr-1 overscroll-contain">
                @if($isKaryawan)
                    <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Karyawan</p>

                    <x-nav-link :href="route('karyawan.dashboard')" :active="request()->routeIs('karyawan.dashboard')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium" @click="openMobile=false">
                        <i class="fas fa-grid-2 mr-3 w-5"></i> {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('karyawan.attendance.checkin')" :active="request()->routeIs('karyawan.attendance.*')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium" @click="openMobile=false">
                        <i class="fas fa-fingerprint mr-3 w-5"></i> {{ __('Absensi') }}
                    </x-nav-link>

                    <x-nav-link :href="route('karyawan.agenda.index')" :active="request()->routeIs('karyawan.agenda.*')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium" @click="openMobile=false">
                        <i class="fas fa-calendar-alt mr-3 w-5"></i> {{ __('Agenda') }}
                    </x-nav-link>

                    <x-nav-link :href="route('karyawan.chat.index')" :active="request()->routeIs('karyawan.chat.*')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium" @click="openMobile=false">
                        <i class="fas fa-comments mr-3 w-5"></i> {{ __('Chat Group') }}
                    </x-nav-link>

                    <x-nav-link :href="route('karyawan.profile')" :active="request()->routeIs('karyawan.profile')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium" @click="openMobile=false">
                        <i class="fas fa-user mr-3 w-5"></i> {{ __('Profil') }}
                    </x-nav-link>

                    <p class="px-3 pb-2 pt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pekerjaan</p>

                    <x-nav-link :href="route('technician.dashboard')" :active="request()->routeIs('technician.dashboard')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium" @click="openMobile=false">
                        <i class="fas fa-stopwatch mr-3 w-5"></i> {{ __('Tracker Kerja') }}
                    </x-nav-link>

                    <x-nav-link :href="route('jobs.create')" :active="request()->routeIs('jobs.create')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium" @click="openMobile=false">
                        <i class="fas fa-plus-circle mr-3 w-5"></i> {{ __('Buat Tugas Baru') }}
                    </x-nav-link>
                @else
                    <p class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Utama</p>

                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                        class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium transition-colors duration-200" @click="openMobile=false">
                        <i class="fas fa-home mr-3 w-5"></i> {{ __('Dashboard') }}
                    </x-nav-link>

                    @if($isKepala)
                        <x-nav-link :href="route('admin.messages')" :active="request()->routeIs('admin.messages')"
                            class="flex w-full items-center justify-start rounded-lg border-none px-3 py-2 text-sm font-medium transition-colors duration-200" @click="openMobile=false">
                            <i class="fas fa-comments mr-3 w-5"></i> {{ __('Chat Group') }}
                        </x-nav-link>
                    @endif
                @endif
            </nav>

            <div class="border-t border-slate-200 pt-4 dark:border-slate-700">
                <div class="mb-4 px-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-600">{{ $role }}</p>
                    <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-100">{{ Auth::user()->name }}</p>
                </div>
                <div class="px-3 pb-3 flex items-center gap-2">
                    <button
                        type="button"
                        data-theme-toggle
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        aria-label="Ganti tema"
                        title="Dark/Light"
                    >
                        <i class="fas fa-moon text-[13px]" data-theme-icon aria-hidden="true"></i>
                        <span class="sr-only" data-theme-label>Dark</span>
                    </button>

                    <form method="POST" action="{{ route('logout') }}" class="flex-1" data-logout-confirm>
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-500/10">
                            <i class="fas fa-sign-out-alt mr-2"></i> {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
</div>
