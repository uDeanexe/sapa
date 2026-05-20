<x-app-layout>
    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">{{ __('Pemberitahuan & Berita') }}</h2>
                        <p class="admin-subtitle">{{ __('Informasi terbaru dan pengumuman perusahaan') }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters (UI only) -->
            <div class="admin-card mb-6 p-4">
                <div class="flex flex-wrap gap-2">
                    <button class="rounded-full border-2 border-sky-600 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700 dark:border-sky-300/40 dark:bg-sky-500/10 dark:text-sky-200">
                        {{ __('Semua') }}
                    </button>
                    <button class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300 dark:border-slate-800 dark:text-slate-300">
                        {{ __('Pengumuman') }}
                    </button>
                    <button class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300 dark:border-slate-800 dark:text-slate-300">
                        {{ __('Update Sistem') }}
                    </button>
                    <button class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300 dark:border-slate-800 dark:text-slate-300">
                        {{ __('Policy') }}
                    </button>
                    <button class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300 dark:border-slate-800 dark:text-slate-300">
                        {{ __('Event') }}
                    </button>
                </div>
            </div>

            <!-- News List -->
            <div class="space-y-4">
                @forelse($notifications ?? [] as $notification)
                    <div class="admin-card hover:shadow-md transition overflow-hidden">
                        <div class="flex gap-4 p-6">
                        <!-- Icon -->
                        @php
                            $category = $notification->category ?? 'general';
                            $iconClass = match ($category) {
                                'announcement' => 'bg-sky-50 text-sky-600',
                                'system' => 'bg-emerald-50 text-emerald-600',
                                'policy' => 'bg-amber-50 text-amber-600',
                                'event' => 'bg-purple-50 text-purple-600',
                                default => 'bg-slate-50 text-slate-600',
                            };
                            $badgeClass = match ($category) {
                                'announcement' => 'bg-sky-50 text-sky-700',
                                'system' => 'bg-emerald-50 text-emerald-700',
                                'policy' => 'bg-amber-50 text-amber-700',
                                'event' => 'bg-purple-50 text-purple-700',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp

                        <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-lg {{ $iconClass }}">
                            @if($notification->category === 'announcement')
                                <i class="fas fa-bullhorn text-2xl"></i>
                            @elseif($notification->category === 'system')
                                <i class="fas fa-cogs text-2xl"></i>
                            @elseif($notification->category === 'policy')
                                <i class="fas fa-file-contract text-2xl"></i>
                            @else
                                <i class="fas fa-star text-2xl"></i>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $notification->title }}</h3>
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ Str::limit($notification->content, 150) }}</p>
                                </div>
                                @if(!$notification->is_read)
                                    <span class="flex h-3 w-3 flex-shrink-0 rounded-full bg-sky-600"></span>
                                @endif
                            </div>

                            <!-- Meta -->
                            <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-500">
                                <div>
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $notification->created_at->format('d M Y') }}
                                </div>
                                <div>
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $notification->created_at->format('H:i') }}
                                </div>
                                <div>
                                    <i class="fas fa-tag mr-1"></i>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $category)) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                            <!-- Action -->
                            <div class="flex flex-shrink-0">
                                <a href="{{ route('karyawan.notifications.show', $notification->id) }}" class="text-sky-600 hover:text-sky-700 font-medium text-sm dark:text-sky-200 dark:hover:text-sky-100">
                                    {{ __('Baca') }} <i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="admin-card p-12 text-center">
                        <i class="fas fa-inbox text-6xl text-slate-300 mb-4 block dark:text-slate-600"></i>
                        <p class="text-slate-600 dark:text-slate-300">{{ __('Tidak ada pemberitahuan saat ini') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $notifications->links() ?? '' }}
            </div>
        </div>
    </div>
</x-app-layout>
