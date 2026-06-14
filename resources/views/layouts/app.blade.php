<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="{{ $description ?? config('app.description', 'Sapa Jonusa - Sistem Manajemen Komunikasi') }}">
        <meta name="theme-color" content="#1f2937">
        
        @auth
            <meta name="app-user-id" content="{{ auth()->id() }}">
            <meta name="user-role" content="{{ auth()->user()->role ?? 'user' }}">
        @endauth

        <title>
            @isset($title)
                {{ $title }} | {{ config('app.name', 'Sapa Jonusa') }}
            @else
                {{ config('app.name', 'Sapa Jonusa') }}
            @endisset
        </title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('assets/img/jonusa.png') }}" sizes="32x32">
        <link rel="apple-touch-icon" href="{{ asset('assets/img/jonusa.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        
        <!-- Vite Assets -->
        @vite(['resources/css/app.css', 'resources/css/chat-fix.css', 'resources/js/app.js'])

        <!-- Theme bootstrap (prevents flash) -->
        <script>
            (function () {
                try {
                    const stored = localStorage.getItem('theme');
                    const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const theme = stored === 'dark' || stored === 'light' ? stored : (systemDark ? 'dark' : 'light');
                    document.documentElement.classList.toggle('dark', theme === 'dark');
                    document.documentElement.style.colorScheme = theme;
                } catch (_) {}
            })();
        </script>

        <!-- Additional Styles -->
        @stack('styles')
    </head>
    
    <body class="font-sans antialiased text-gray-900 bg-gray-50 dark:bg-[#0b1220] dark:text-slate-100">
        @php
            $isChatRoute = request()->routeIs('admin.messages') || request()->routeIs('karyawan.chat.*');
        @endphp

        <div
            class="flex {{ $isChatRoute ? 'h-screen overflow-hidden' : 'min-h-screen' }} bg-slate-50 dark:bg-[#0b1220]"
            x-data="{
                openKehadiran: {{ (request()->routeIs('admin.presence.*') || request()->routeIs('admin.perizinan')) ? 'true' : 'false' }},
                openPekerjaan: {{ (request()->routeIs('jobs.*') || request()->routeIs('admin.clients.*')) ? 'true' : 'false' }},
                openRekrutmen: {{ request()->routeIs('recruitment.*') ? 'true' : 'false' }},
                openKpi: {{ request()->routeIs('kpi.*') ? 'true' : 'false' }},
                openMobile: false
            }"
        >
            <!-- Sidebar + mobile drawer -->
            @include('layouts.navigation')

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="flex items-center justify-between border-b bg-white p-4 text-slate-800 md:hidden dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700">
                    <x-application-logo class="h-8 w-auto" />
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            data-theme-toggle
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10"
                            aria-label="Ganti tema"
                            title="Dark/Light"
                        >
                            <i class="fas fa-moon text-lg" data-theme-icon aria-hidden="true"></i>
                        </button>
                        <button type="button" @click="openMobile = !openMobile" class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10" aria-label="Buka menu">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </header>

                <main id="main-content" class="flex flex-col flex-1 min-w-0 transition-colors duration-300 {{ $isChatRoute ? 'overflow-hidden p-0' : 'p-4 md:p-6' }}">
                    @auth
                        <!-- Notifications Toast Container -->
                        <section
                            id="global-chat-toast-wrap"
                            class="global-chat-toast-wrap"
                            role="region"
                            aria-live="polite"
                            aria-label="Notifications"
                            data-server-notification-poll
                            data-poll-url="{{ route('web.notifications.poll') }}"
                            data-enabled="true"
                        ></section>
                    @endauth

                    <article class="{{ $isChatRoute ? 'flex flex-col flex-1 overflow-hidden' : 'py-3 sm:py-4' }}">
                        {{ $slot }}
                    </article>
                </main>

                @unless($isChatRoute)
                    {{-- <footer class="mt-auto bg-white border-t border-gray-200 py-6">
                        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                            <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-600">
                                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Sapa Jonusa') }}. Semua hak dilindungi.</p>
                                <nav class="mt-4 sm:mt-0 flex gap-6">
                                    <a href="#" class="hover:text-gray-900 transition-colors">Privasi</a>
                                    <a href="#" class="hover:text-gray-900 transition-colors">Ketentuan</a>
                                </nav>
                            </div>
                        </div>
                    </footer> --}}
                @endunless
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
