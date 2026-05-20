<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="{{ $description ?? 'Masuk ke Sapa Jonusa - Sistem Manajemen Komunikasi Terpadu' }}">
        <meta name="theme-color" content="#1f2937">

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
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Additional Styles -->
        @stack('styles')

        <style>
            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                z-index: -10;
            }
        </style>
    </head>
    
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500">
        <!-- Skip to content link for accessibility -->
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-0 focus:left-0 focus:z-50 focus:p-4 focus:bg-white focus:text-gray-900">
            Lompat ke konten utama
        </a>

        <!-- Main Content -->
        <main id="main-content" class="min-h-screen flex flex-col items-center justify-center bg-transparent px-4 py-6 sm:py-12">
            <article class="w-full max-w-md">
                <!-- Logo Section -->
                @if($showLogo ?? true)
                    <section class="mb-8 text-center">
                        <a href="/" class="inline-flex items-center justify-center group">
                            <div class="relative">
                                <div class="absolute inset-0 bg-white rounded-full opacity-20 group-hover:opacity-30 transition-opacity duration-200"></div>
                                <x-application-logo class="w-16 h-16 sm:w-20 sm:h-20 fill-current text-white relative" />
                            </div>
                        </a>
                    </section>
                @endif

                <!-- Title Section -->
                @isset($title)
                    <section class="mb-6 text-center">
                        <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">{{ $title }}</h1>
                        @isset($subtitle)
                            <p class="text-indigo-100 text-sm sm:text-base">{{ $subtitle }}</p>
                        @endisset
                    </section>
                @endif

                <!-- Form Container -->
                <section class="bg-white rounded-2xl shadow-2xl overflow-hidden backdrop-blur-sm">
                    <div class="p-6 sm:p-8">
                        {{ $slot }}
                    </div>

                    <!-- Register prompt removed by request -->
                </section>

                <!-- Footer links removed by request -->
            </article>
        </main>

        <!-- Scripts -->
        @stack('scripts')
    </body>
</html>
