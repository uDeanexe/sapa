<x-guest-layout :title="'Masuk ke Akun'" :subtitle="'Masukkan email dan password untuk melanjutkan.'" :showLogo="false">
    <div class="text-center">
        <a href="/" class="mx-auto inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-100 bg-white p-2 shadow-lg shadow-slate-200/80">
            <img
                src="{{ asset('assets/img/jonusa.png') }}"
                alt="{{ config('app.name', 'Logo') }} logo"
                class="h-full w-full object-contain"
            >
        </a>
        <h2 class="mt-5 text-xl font-black tracking-tight text-slate-950">Masuk</h2>
        <p class="mt-1 text-sm text-slate-500">Gunakan akun yang terdaftar.</p>
    </div>

    <div class="mt-6">
        <x-auth-session-status class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" :status="session('status')" />

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email</label>
                <input
                    id="email"
                    class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-emerald-500"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@perusahaan.com"
                    required
                    autofocus
                    autocomplete="username"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div x-data="{ showPassword: false }">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label for="password" class="block text-sm font-bold text-slate-700">Password</label>
                    @if (Route::has('password.request'))
                        <a class="text-xs font-bold text-emerald-700 hover:text-emerald-900" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    @endif
                </div>

                <div class="relative">
                    <input
                        id="password"
                        class="block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 pr-12 text-sm font-medium text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-emerald-500"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        name="password"
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                    />

                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-slate-400 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-500"
                        x-on:click="showPassword = !showPassword"
                        x-bind:aria-label="showPassword ? 'Sembunyikan password' : 'Lihat password'"
                    >
                        <svg x-show="!showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M2.75 12s3.25-6.25 9.25-6.25S21.25 12 21.25 12 18 18.25 12 18.25 2.75 12 2.75 12Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg x-cloak x-show="showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m4 4 16 16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                            <path d="M10.58 10.58A2 2 0 0 0 13.42 13.4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                            <path d="M8.12 5.93A9.6 9.6 0 0 1 12 5.25c6 0 9.25 6.75 9.25 6.75a16.5 16.5 0 0 1-2.08 2.95M15.54 17.1a9.8 9.8 0 0 1-3.54.65C6 17.75 2.75 12 2.75 12a16.7 16.7 0 0 1 3.42-4.08" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500" name="remember">
                    <span class="ms-2 text-sm font-semibold text-slate-600">Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Masuk
            </button>
        </form>
    </div>
</x-guest-layout>
