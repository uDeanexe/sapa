<x-app-layout>
    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">{{ __('Detail Pemberitahuan') }}</h2>
                        <p class="admin-subtitle">{{ __('Baca informasi dan lampiran terkait.') }}</p>
                    </div>
                    <a href="{{ route('karyawan.notifications') }}" class="btn-secondary-soft">
                        <i class="fas fa-chevron-left mr-2"></i> {{ __('Kembali') }}
                    </a>
                </div>
            </div>

            <!-- Content -->
            <div class="admin-card overflow-hidden">
            <!-- Header -->
            <div class="border-b border-slate-200 bg-gradient-to-r from-sky-50 to-blue-50 p-6 dark:border-slate-800 dark:from-sky-500/10 dark:to-blue-500/10">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="mb-2">
                            @php
                                $category = $notification->category ?? 'general';
                                $badgeClass = match ($category) {
                                    'announcement' => 'bg-sky-50 text-sky-700',
                                    'system' => 'bg-emerald-50 text-emerald-700',
                                    'policy' => 'bg-amber-50 text-amber-700',
                                    'event' => 'bg-purple-50 text-purple-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp

                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $badgeClass }}">
                                {{ ucfirst(str_replace('_', ' ', $category)) }}
                            </span>
                        </div>
                        <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $notification->title }}</h1>
                        <div class="mt-3 flex flex-wrap gap-4 text-sm text-slate-600">
                            <div>
                                <i class="fas fa-calendar mr-2"></i>
                                {{ $notification->created_at->format('l, d M Y') }}
                            </div>
                            <div>
                                <i class="fas fa-clock mr-2"></i>
                                {{ $notification->created_at->format('H:i') }}
                            </div>
                            @if($notification->author)
                                <div>
                                    <i class="fas fa-user mr-2"></i>
                                    {{ $notification->author->name }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button class="rounded-lg border border-slate-200 p-2 hover:bg-slate-100 transition" title="{{ __('Bagikan') }}">
                            <i class="fas fa-share-alt text-slate-600"></i>
                        </button>
                        <button class="rounded-lg border border-slate-200 p-2 hover:bg-slate-100 transition" title="{{ __('Tandai sebagai penting') }}">
                            <i class="fas fa-star text-slate-400"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6 prose prose-sm max-w-none dark:prose-invert">
                <!-- Featured Image -->
                @if($notification->image)
                    <img src="{{ $notification->image }}" alt="{{ $notification->title }}" class="rounded-lg mb-6 w-full max-h-96 object-cover">
                @endif

                <!-- Content -->
                <div class="text-slate-700 leading-relaxed">
                    {!! nl2br(e($notification->content)) !!}
                </div>

                <!-- Attachment -->
                @if($notification->attachment)
                    <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-300/30 dark:bg-amber-500/10">
                        <p class="text-sm font-semibold text-amber-900 mb-3 dark:text-amber-200">{{ __('Lampiran') }}</p>
                        <a href="{{ $notification->attachment }}" class="inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 font-medium text-sm dark:text-sky-200 dark:hover:text-sky-100">
                            <i class="fas fa-download"></i> {{ __('Unduh Lampiran') }}
                        </a>
                    </div>
                @endif

                <!-- Related -->
                @if($relatedNotifications->count() > 0)
                    <div class="mt-8 border-t border-slate-200 pt-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4 dark:text-slate-100">{{ __('Berita Terkait') }}</h3>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach($relatedNotifications as $related)
                                <a href="{{ route('karyawan.notifications.show', $related->id) }}" class="rounded-lg border border-slate-200 p-3 hover:shadow-md transition dark:border-slate-800 dark:hover:bg-white/5">
                                    <p class="font-medium text-slate-900 text-sm dark:text-slate-100">{{ Str::limit($related->title, 50) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $related->created_at->format('d M Y') }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="border-t border-slate-200 bg-slate-50 p-6 flex items-center justify-between dark:border-slate-800">
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <i class="fas fa-info-circle"></i>
                    {{ __('Pemberitahuan ini dibaca pada') }} {{ $notification->updated_at->format('d M Y H:i') }}
                </div>
                <a href="{{ route('karyawan.notifications') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-medium text-slate-900 hover:bg-slate-300 transition dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                    {{ __('Kembali ke Daftar') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
