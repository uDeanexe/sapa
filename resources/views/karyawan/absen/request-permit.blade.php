<x-app-layout>
    <div class="admin-shell attendance-shell">
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Ajukan Izin / Cuti</h2>
                        <p class="admin-subtitle">Kirim permohonan dengan alasan dan lampiran agar bisa direview admin.</p>
                    </div>
                    <a href="{{ route('karyawan.attendance.index') }}" class="btn-secondary-soft">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </div>

            @if($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_360px]">
                <section class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Form Permohonan</h3>
                    </div>

                    <form action="{{ route('karyawan.attendance.submit-permit') }}" method="POST" enctype="multipart/form-data" class="modal-form">
                        @csrf

                        <div>
                            <label class="field-label">Jenis Permohonan</label>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <label class="attendance-choice">
                                    <input type="radio" name="type" value="izin" required>
                                    <span><i class="fas fa-briefcase"></i></span>
                                    <strong>Izin Kerja</strong>
                                    <small>Izin tidak masuk kerja.</small>
                                </label>
                                <label class="attendance-choice">
                                    <input type="radio" name="type" value="cuti" required>
                                    <span><i class="fas fa-calendar-days"></i></span>
                                    <strong>Cuti</strong>
                                    <small>Cuti tahunan atau khusus.</small>
                                </label>
                                <label class="attendance-choice">
                                    <input type="radio" name="type" value="sakit" required>
                                    <span><i class="fas fa-kit-medical"></i></span>
                                    <strong>Sakit</strong>
                                    <small>Lampirkan bukti bila ada.</small>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="start_date" class="field-label">Dari Tanggal</label>
                                <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required class="app-input">
                            </div>
                            <div>
                                <label for="end_date" class="field-label">Sampai Tanggal</label>
                                <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required class="app-input">
                            </div>
                        </div>

                        <div>
                            <label for="reason" class="field-label">Alasan</label>
                            <textarea id="reason" name="reason" rows="5" required class="app-input" placeholder="Jelaskan alasan permohonan...">{{ old('reason') }}</textarea>
                        </div>

                        <div>
                            <label for="attachment" class="field-label">Lampiran</label>
                            <label for="attachment" class="upload-box block cursor-pointer text-center hover:border-emerald-400 hover:bg-emerald-50/50">
                                <i class="fas fa-cloud-arrow-up mb-2 block text-3xl text-slate-400"></i>
                                <span id="attachment-label" class="block text-sm font-semibold text-slate-800">Pilih PDF atau foto bukti</span>
                                <span class="mt-1 block text-xs text-slate-500">Maksimal 5MB. Format: PDF, JPG, PNG.</span>
                            </label>
                            <input type="file" id="attachment" name="attachment" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <a href="{{ route('karyawan.attendance.index') }}" class="btn-secondary-soft">Batal</a>
                            <button type="submit" class="btn-primary-soft">
                                <i class="fas fa-paper-plane mr-2"></i> Ajukan
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="space-y-4">
                    <div class="metric-card metric-amber">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="metric-label">Sisa Cuti</p>
                                <p class="metric-value">{{ $leaveQuota['remaining'] ?? 0 }}</p>
                                <p class="metric-note">dari {{ $leaveQuota['annual'] ?? 0 }} hari, terpakai {{ $leaveQuota['used'] ?? 0 }} hari.</p>
                            </div>
                            <span class="metric-icon"><i class="fas fa-calendar-check"></i></span>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">Perhatian</h3>
                        </div>
                        <div class="space-y-3 p-5 text-sm text-slate-600">
                            <p><i class="fas fa-circle-info mr-2 text-amber-600"></i>Ajukan permohonan lebih awal jika memungkinkan.</p>
                            <p><i class="fas fa-circle-info mr-2 text-amber-600"></i>Lampiran membantu proses approval.</p>
                            <p><i class="fas fa-circle-info mr-2 text-amber-600"></i>Status akan berubah setelah admin melakukan review.</p>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">Permohonan Terakhir</h3>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @forelse($recentRequests ?? [] as $req)
                                <div class="p-4 text-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="font-semibold capitalize text-slate-900">{{ $req->type }}</span>
                                        <span class="attendance-status-badge status-{{ $req->status }}">{{ ucfirst($req->status) }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}
                                    </p>
                                </div>
                            @empty
                                <div class="p-5 text-sm text-slate-500">Belum ada permohonan.</div>
                            @endforelse
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('attachment')?.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            const label = document.getElementById('attachment-label');
            if (file && label) label.textContent = file.name;
        });
    </script>
</x-app-layout>
