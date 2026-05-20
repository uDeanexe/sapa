<x-app-layout>
    @php
        $checkInTime = $todayAttendance?->check_in ? \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i') : null;
        $checkOutTime = $todayAttendance?->check_out ? \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i') : null;
        $isCheckingOut = $todayAttendance && $todayAttendance->check_in && ! $todayAttendance->check_out;
        $isDone = $todayAttendance && $todayAttendance->check_out;
        $formAction = $isCheckingOut ? route('karyawan.attendance.store-checkout') : route('karyawan.attendance.store-checkin');
    @endphp

    <div class="admin-shell attendance-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Absensi Karyawan</h2>
                        <p class="admin-subtitle">{{ now()->translatedFormat('l, d F Y') }}. Foto dan lokasi wajib dikirim untuk approval admin.</p>
                    </div>
                    <a href="{{ route('karyawan.attendance.index') }}" class="btn-secondary-soft">
                        <i class="fas fa-clock-rotate-left mr-2"></i> Riwayat
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert-error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
                <section class="admin-card p-6">
                    <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                <div class="text-center leading-none">
                                    <p id="time" class="text-xl font-black tracking-tight">{{ now()->format('H:i') }}</p>
                                    <p class="mt-1 text-[11px] font-semibold text-slate-500">Jam lokal</p>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Status hari ini</p>
                                @if($isDone)
                                    <h3 class="mt-1 text-lg font-semibold text-slate-950">Absensi selesai</h3>
                                    <p class="mt-1 text-sm text-slate-600">Masuk {{ $checkInTime }} dan pulang {{ $checkOutTime }}.</p>
                                @elseif($isCheckingOut)
                                    <h3 class="mt-1 text-lg font-semibold text-slate-950">Sedang bekerja</h3>
                                    <p class="mt-1 text-sm text-slate-600">Check in tercatat pukul {{ $checkInTime }}. Kirim foto dan lokasi untuk check out.</p>
                                @else
                                    <h3 class="mt-1 text-lg font-semibold text-slate-950">Belum check in</h3>
                                    <p class="mt-1 text-sm text-slate-600">Ambil foto bukti hadir dan izinkan lokasi sebelum mengirim.</p>
                                @endif
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700">
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                    </div>

                    @if(! $isDone)
                        <form id="attendance-form" action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-5">
                            @csrf
                            <input type="hidden" name="latitude" id="latitude-input">
                            <input type="hidden" name="longitude" id="longitude-input">

                            <div>
                                <label for="photo-input" class="block">
                                    <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-5 hover:border-emerald-300 hover:bg-emerald-50/40 transition">
                                        <div class="flex items-center gap-4">
                                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-white text-slate-700 shadow-sm">
                                                <i class="fas fa-camera text-lg"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-900">Ambil foto bukti</p>
                                                <p class="mt-0.5 text-xs text-slate-600">Gunakan kamera atau pilih foto dari perangkat.</p>
                                            </div>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <button id="open-camera" type="button" class="btn-secondary-soft text-xs px-3 py-2">
                                                <i class="fas fa-camera mr-2"></i> Buka Kamera
                                            </button>
                                            <button id="choose-file" type="button" class="btn-secondary-soft text-xs px-3 py-2">
                                                <i class="fas fa-folder-open mr-2"></i> Pilih File
                                            </button>
                                            <span class="text-xs text-slate-500">Catatan: di desktop biasanya akan muncul file picker.</span>
                                        </div>
                                        <img id="photo-preview" class="hidden mt-4 max-h-64 w-full rounded-xl object-contain bg-white border border-slate-200" alt="Preview foto absensi">
                                    </div>
                                </label>
                                <input id="photo-input" name="photo" type="file" accept="image/*" capture="environment" class="sr-only" required>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                                        <i class="fas fa-location-crosshairs"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Lokasi</p>
                                        <p id="location-text" class="mt-1 break-words text-sm font-semibold text-slate-800">Mendeteksi lokasi...</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="notes" class="field-label">Catatan</label>
                                <textarea id="notes" name="notes" rows="4" class="app-input" placeholder="Opsional, contoh: WFO dari kantor pusat">{{ old('notes') }}</textarea>
                            </div>

                            <button id="attendance-submit" type="submit" class="btn-primary-soft w-full disabled:cursor-not-allowed disabled:opacity-60" disabled>
                                <i class="fas {{ $isCheckingOut ? 'fa-right-from-bracket' : 'fa-right-to-bracket' }} mr-2"></i>
                                {{ $isCheckingOut ? 'Check Out Sekarang' : 'Check In Sekarang' }}
                            </button>

                            <a href="{{ route('karyawan.attendance.request-permit') }}" class="btn-secondary-soft w-full">
                                <i class="fas fa-file-signature mr-2"></i> Ajukan Izin/Cuti
                            </a>
                        </form>
                    @else
                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Masuk</p>
                                <p class="mt-1 text-lg font-black text-slate-950">{{ $checkInTime }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Pulang</p>
                                <p class="mt-1 text-lg font-black text-slate-950">{{ $checkOutTime }}</p>
                            </div>
                        </div>
                    @endif
                </section>

                <aside class="space-y-4">
                    <div class="metric-card metric-emerald">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="metric-label">Approval Admin</p>
                                <p class="metric-note">Data foto, lokasi, dan waktu masuk/pulang akan masuk ke daftar persetujuan admin.</p>
                            </div>
                            <span class="metric-icon"><i class="fas fa-user-check"></i></span>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">Checklist Sebelum Kirim</h3>
                        </div>
                        <div class="space-y-3 p-5 text-sm text-slate-600">
                            <p><i class="fas fa-check text-emerald-600 mr-2"></i>Lokasi browser aktif.</p>
                            <p><i class="fas fa-check text-emerald-600 mr-2"></i>Foto bukti jelas.</p>
                            <p><i class="fas fa-check text-emerald-600 mr-2"></i>Koneksi stabil saat submit.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <script>
        const timeEl = document.getElementById('time');
        const locationText = document.getElementById('location-text');
        const latitudeInput = document.getElementById('latitude-input');
        const longitudeInput = document.getElementById('longitude-input');
        const photoInput = document.getElementById('photo-input');
        const photoPreview = document.getElementById('photo-preview');
        const submitButton = document.getElementById('attendance-submit');
        const openCameraBtn = document.getElementById('open-camera');
        const chooseFileBtn = document.getElementById('choose-file');

        let cameraStream = null;

        function stopCameraStream() {
            if (!cameraStream) return;
            for (const track of cameraStream.getTracks()) {
                try { track.stop(); } catch {}
            }
            cameraStream = null;
        }

        function ensureCameraModal() {
            let modal = document.getElementById('attendance-camera-modal');
            if (modal) return modal;

            modal = document.createElement('div');
            modal.id = 'attendance-camera-modal';
            modal.className = 'modal-backdrop';
            modal.innerHTML = `
                <div class="modal-panel" style="max-width: 520px;">
                    <div class="modal-header flex items-start justify-between gap-4">
                        <div>
                            <div class="modal-title">Ambil Foto</div>
                            <div class="modal-subtitle">Gunakan kamera perangkat, lalu ambil foto.</div>
                        </div>
                        <button type="button" class="modal-close" id="attendance-camera-close" aria-label="Tutup">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body space-y-4">
                        <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-950/90">
                            <video id="attendance-camera-video" autoplay playsinline class="w-full" style="max-height: 320px;"></video>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 sm:justify-end">
                            <button type="button" class="btn-secondary-soft" id="attendance-camera-cancel">Batal</button>
                            <button type="button" class="btn-primary-soft" id="attendance-camera-capture">
                                <i class="fas fa-camera mr-2"></i> Ambil
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            const close = () => {
                stopCameraStream();
                modal.remove();
            };

            modal.addEventListener('click', (e) => {
                if (e.target === modal) close();
            });
            modal.querySelector('#attendance-camera-close')?.addEventListener('click', close);
            modal.querySelector('#attendance-camera-cancel')?.addEventListener('click', close);

            return modal;
        }

        function updateClock() {
            if (!timeEl) return;
            const now = new Date();
            timeEl.textContent = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
        }

        function updateSubmitState() {
            if (!submitButton) return;
            submitButton.disabled = !(latitudeInput?.value && longitudeInput?.value && photoInput?.files?.length);
        }

        setInterval(updateClock, 1000);
        updateClock();

        chooseFileBtn?.addEventListener('click', () => {
            photoInput?.click();
        });

        openCameraBtn?.addEventListener('click', async () => {
            if (!navigator.mediaDevices?.getUserMedia) {
                photoInput?.click();
                return;
            }

            const modal = ensureCameraModal();
            const video = modal.querySelector('#attendance-camera-video');
            const captureBtn = modal.querySelector('#attendance-camera-capture');

            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false,
                });
                if (video) video.srcObject = cameraStream;
            } catch (e) {
                stopCameraStream();
                try { modal.remove(); } catch {}
                photoInput?.click();
                return;
            }

            captureBtn?.addEventListener('click', () => {
                if (!video) return;
                const canvas = document.createElement('canvas');
                const w = video.videoWidth || 1280;
                const h = video.videoHeight || 720;
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx?.drawImage(video, 0, 0, w, h);
                canvas.toBlob((blob) => {
                    if (!blob) return;
                    const file = new File([blob], `attendance_${Date.now()}.jpg`, { type: blob.type || 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    if (photoInput) photoInput.files = dt.files;
                    if (photoPreview) {
                        photoPreview.src = URL.createObjectURL(file);
                        photoPreview.classList.remove('hidden');
                    }
                    stopCameraStream();
                    modal.remove();
                    updateSubmitState();
                }, 'image/jpeg', 0.9);
            }, { once: true });
        });

        photoInput?.addEventListener('change', () => {
            const file = photoInput.files?.[0];
            if (!file || !photoPreview) {
                updateSubmitState();
                return;
            }

            photoPreview.src = URL.createObjectURL(file);
            photoPreview.classList.remove('hidden');
            updateSubmitState();
        });

        if (navigator.geolocation && latitudeInput && longitudeInput) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    latitudeInput.value = lat;
                    longitudeInput.value = lon;
                    locationText.textContent = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
                    updateSubmitState();
                },
                () => {
                    locationText.textContent = 'Lokasi tidak terbaca. Izinkan akses lokasi browser.';
                    updateSubmitState();
                },
                { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 },
            );
        } else if (locationText) {
            locationText.textContent = 'Browser tidak mendukung deteksi lokasi.';
        }
    </script>
</x-app-layout>
