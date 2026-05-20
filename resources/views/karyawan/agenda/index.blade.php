<x-app-layout>
    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">{{ __('Agenda & Event') }}</h2>
                        <p class="admin-subtitle">{{ __('Jadwal dan acara penting') }}</p>
                    </div>
                </div>
            </div>

            <!-- Calendar View & List -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Calendar -->
            <div class="admin-card">
                <div class="admin-card-header p-4">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Kalender') }}</h2>
                </div>
                <div class="p-4">
                    <div id="calendar" class="space-y-2">
                        <!-- Calendar will be rendered here -->
                        <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-slate-600 mb-4">
                            <div>{{ __('Sen') }}</div>
                            <div>{{ __('Sel') }}</div>
                            <div>{{ __('Rab') }}</div>
                            <div>{{ __('Kam') }}</div>
                            <div>{{ __('Jum') }}</div>
                            <div>{{ __('Sab') }}</div>
                            <div>{{ __('Min') }}</div>
                        </div>
                        @php
                            $now = now();
                            $firstDay = $now->copy()->startOfMonth();
                            $lastDay = $now->copy()->endOfMonth();
                            $startDate = $firstDay->copy()->startOfWeek();
                            $endDate = $lastDay->copy()->endOfWeek();
                        @endphp
                        <div class="grid grid-cols-7 gap-1">
                            @for($date = $startDate; $date <= $endDate; $date = $date->addDay())
                                <button class="rounded p-2 text-sm {{ $date->month === $now->month ? 'hover:bg-slate-100' : 'text-slate-300' }} {{ $date->isSameDay($now) ? 'bg-sky-600 text-white font-bold' : '' }}">
                                    {{ $date->day }}
                                </button>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <!-- Events List -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Filter -->
                <div class="admin-card p-4">
                    <div class="flex gap-2 flex-wrap">
                        <button class="rounded-full border-2 border-sky-600 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700">
                            {{ __('Semua') }}
                        </button>
                        <button class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300">
                            {{ __('Rapat') }}
                        </button>
                        <button class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300">
                            {{ __('Training') }}
                        </button>
                        <button class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:border-slate-300">
                            {{ __('Event') }}
                        </button>
                    </div>
                </div>

                <!-- Events -->
                <div class="space-y-3">
                    @forelse($events ?? [] as $event)
                        <div class="admin-card p-4 hover:shadow-md transition">
                            <div class="flex gap-4">
                                @php
                                    $type = $event->type ?? 'event';
                                    $iconClass = match ($type) {
                                        'meeting' => 'bg-sky-50 text-sky-600',
                                        'training' => 'bg-emerald-50 text-emerald-600',
                                        default => 'bg-amber-50 text-amber-600',
                                    };
                                @endphp

                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg {{ $iconClass }}">
                                    @if($event->type === 'meeting')
                                        <i class="fas fa-users text-lg"></i>
                                    @elseif($event->type === 'training')
                                        <i class="fas fa-graduation-cap text-lg"></i>
                                    @else
                                        <i class="fas fa-calendar-alt text-lg"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-900">{{ $event->title }}</h3>
                                    <p class="mt-1 text-sm text-slate-600">{{ $event->description }}</p>
                                    <div class="mt-2 flex flex-wrap gap-3 text-xs text-slate-500">
                                        <div>
                                            <i class="fas fa-calendar mr-1"></i>
                                            {{ $event->start_date->format('d M Y') }}
                                        </div>
                                        <div>
                                            <i class="fas fa-clock mr-1"></i>
                                            @if(isset($event->start_time, $event->end_time) && $event->start_time && $event->end_time)
                                                {{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }}
                                            @else
                                                Seharian
                                            @endif
                                        </div>
                                        @if($event->location)
                                            <div>
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $event->location }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-shrink-0 items-start gap-2">
                                    @if(!$event->user_has_rsvp)
                                        <button class="rounded px-3 py-1 text-xs font-medium border border-slate-200 text-slate-600 hover:bg-slate-50">
                                            {{ __('RSVP') }}
                                        </button>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                            ✓ {{ __('Hadir') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="admin-card p-8 text-center">
                            <i class="fas fa-calendar-times text-4xl text-slate-300 mb-3 block"></i>
                            <p class="text-slate-600">{{ __('Tidak ada agenda untuk saat ini') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
