<x-app-layout>
    @php
        $fridayHolidayCount = collect($events)->filter(fn ($event) => $event['type'] === 'holiday' && str_contains(strtolower($event['title'] ?? ''), 'jumat'))->count();
        $manualHolidayCount = collect($events)->where('type', 'holiday')->count() - $fridayHolidayCount;
        $workEventCount = $jobEventCount ?? 0;
    @endphp

    <div class="admin-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Kalender Jadwal Kerja</h2>
                        <p class="admin-subtitle">Atur jadwal kerja, libur, dan reminder tugas dengan tanggal Indonesia.</p>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2">
                        <span class="app-badge bg-rose-100 text-rose-700">
                            <i class="fas fa-calendar-xmark mr-1"></i> Jumat Libur
                        </span>
                        <span class="app-badge-success">
                            Sabtu & Minggu Kerja
                        </span>
                    </div>
                </div>
            </div>

            <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="app-surface p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Jumat Otomatis Libur</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                            <i class="fas fa-calendar-xmark"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $fridayHolidayCount }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Tanggal Jumat dalam tahun berjalan dikunci sebagai hari libur.</p>
                </div>

                <div class="app-surface p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Libur Manual</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <i class="fas fa-calendar-plus"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $manualHolidayCount }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Tanggal selain Jumat yang ditandai sebagai libur kantor.</p>
                </div>

                <div class="app-surface p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Info Kerja / Reminder</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <i class="fas fa-bell"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $workEventCount }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Jumlah event tugas dan reminder kerja pada kalender.</p>
                </div>

                <div class="app-surface p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Cara Pakai</p>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                            <i class="fas fa-circle-info"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Klik tanggal selain Jumat untuk mengubah status menjadi libur atau hari kerja.</p>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_320px]">
                <div class="admin-card">
                    <div class="admin-card-header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="admin-card-title">Kalender Kerja</h3>
                            <p class="mt-1 text-xs text-slate-500">Pilih tanggal untuk toggle libur manual. Jumat ditandai merah dan terkunci.</p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                            <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1.5 text-rose-700">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                Libur
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-slate-600 ring-1 ring-slate-200">
                                <span class="h-2.5 w-2.5 rounded-full bg-white ring-1 ring-slate-300"></span>
                                Hari Kerja
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                Hari Ini
                            </span>
                        </div>
                    </div>

                    <div class="p-3 sm:p-5">
                        <div id="calendar" class="schedule-calendar"></div>
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="app-surface p-5">
                        <h3 class="text-sm font-semibold text-slate-950">Aturan Kalender</h3>
                        <div class="mt-4 space-y-4">
                            <div class="flex gap-3">
                                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Jumat selalu libur</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">Tanggal Jumat tidak bisa diubah dari kalender.</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                                    <i class="fas fa-hand-pointer"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Klik tanggal</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">Tanggal selain Jumat bisa dijadikan libur atau dikembalikan ke hari kerja.</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                    <i class="fas fa-mobile-screen"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">Sinkron mobile</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">Pengaturan ini digunakan sebagai jadwal kerja aplikasi mobile.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-5">
                        <p class="text-sm font-semibold text-rose-800">
                            <i class="fas fa-calendar-xmark mr-2"></i> Jumat Libur
                        </p>
                        <p class="mt-2 text-xs leading-5 text-rose-700">Jika user klik hari Jumat, sistem akan menampilkan info bahwa tanggal tersebut tidak dapat diubah.</p>
                    </div>
                </aside>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const eventsData = @json($events);
            const holidaySet = new Set(eventsData.filter(event => event.type === 'holiday').map(holiday => holiday.start));
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                height: 'auto',
                firstDay: 1,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek',
                },
                buttonText: {
                    today: 'Hari ini',
                    month: 'Bulan',
                    week: 'Minggu',
                },
                events: eventsData,
                eventClick: function (info) {
                    const event = info.event;
                    const props = event.extendedProps || {};
                    const formattedDate = event.start
                        ? event.start.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
                        : '';
                    let html = `<div class="text-left">`;
                    html += `<p><strong>Tanggal:</strong> ${formattedDate}</p>`;
                    html += `<p><strong>Jenis:</strong> ${props.type === 'holiday' ? 'Libur' : 'Tugas / Reminder'}</p>`;
                    if (props.status) {
                        html += `<p><strong>Status:</strong> ${props.status}</p>`;
                    }
                    if (props.assignee) {
                        html += `<p><strong>PIC:</strong> ${props.assignee}</p>`;
                    }
                    if (props.location) {
                        html += `<p><strong>Lokasi:</strong> ${props.location}</p>`;
                    }
                    html += `<p class="mt-2"><strong>Detail:</strong> ${event.title}</p>`;
                    if (props.description) {
                        html += `<p class="mt-2 text-sm text-slate-600">${props.description}</p>`;
                    }
                    html += `</div>`;

                    Swal.fire({
                        title: event.title,
                        html,
                        icon: props.type === 'holiday' ? 'info' : 'question',
                        confirmButtonColor: '#059669',
                    });
                },
                dayCellDidMount: function (info) {
                    const dateStr = info.date.toLocaleDateString('en-CA');
                    const day = info.date.getDay();
                    const isFriday = day === 5;
                    const isHoliday = holidaySet.has(dateStr);
                    const number = info.el.querySelector('.fc-daygrid-day-number');

                    if (isFriday || isHoliday) {
                        info.el.classList.add('schedule-day-holiday');
                    }

                    if (isFriday) {
                        info.el.classList.add('schedule-day-friday');
                        info.el.setAttribute('title', 'Jumat otomatis libur');
                    }

                    if (number && (isFriday || isHoliday)) {
                        number.classList.add('schedule-day-number-holiday');
                    }
                },
                dateClick: function (info) {
                    const day = info.date.getDay();
                    const formattedDate = info.date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

                    if (day === 5) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Jumat Libur',
                            text: `Tanggal ${formattedDate} adalah Jumat, otomatis libur dan tidak dapat diubah.`,
                            confirmButtonColor: '#059669',
                        });
                        return;
                    }

                    const isHoliday = holidaySet.has(info.dateStr);
                    const action = isHoliday ? 'kembalikan menjadi hari kerja' : 'jadikan hari libur';

                    Swal.fire({
                        title: 'Update Jadwal?',
                        text: `${formattedDate} akan di-${action}.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Ubah',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                    }).then(result => {
                        if (result.isConfirmed) {
                            toggleHoliday(info.dateStr);
                        }
                    });
                },
            });

            calendar.render();

            function toggleHoliday(date) {
                Swal.showLoading();

                fetch("{{ route('admin.presence.toggle') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ date }),
                })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire({
                            title: data.success ? 'Berhasil' : 'Info',
                            text: data.message,
                            icon: data.success ? 'success' : 'info',
                            timer: 1600,
                            showConfirmButton: false,
                        }).then(() => location.reload());
                    })
                    .catch(() => {
                        Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
                    });
            }
        });
    </script>
</x-app-layout>
