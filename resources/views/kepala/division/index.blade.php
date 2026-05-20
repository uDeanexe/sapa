<x-app-layout>
    <div
        class="admin-shell"
        x-data="{
            createOpen: @js($errors->any()),
            selected: null,
        }"
        @keydown.escape.window="createOpen = false; selected = null"
    >
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Pengaturan Divisi</h2>
                        <p class="admin-subtitle">Kelola divisi dan alur kerja tracker untuk setiap tahap pekerjaan.</p>
                    </div>

                    <button type="button" @click="createOpen = true" class="btn-primary-soft">
                        <i class="fas fa-plus mr-2"></i> Create Divisi
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div id="division-success" class="alert-success flex items-center justify-between gap-4">
                    <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                    <button type="button" data-dismiss="#division-success" class="text-emerald-600 hover:text-emerald-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    <p class="font-semibold">Data belum bisa disimpan.</p>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Total Divisi</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $divisions->count() }}</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Tahap per Divisi</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">4</p>
                </div>
                <div class="app-surface p-5">
                    <p class="text-sm font-medium text-slate-500">Alur Tracker</p>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Klik baris divisi untuk mengatur nama tahap dan syarat deskripsi, foto, atau video.</p>
                </div>
            </section>

            <div x-show="createOpen" x-cloak x-transition.opacity class="modal-backdrop">
                <div
                    @click.outside="createOpen = false"
                    x-transition:enter="transform transition ease-out duration-200"
                    x-transition:enter-start="scale-95 opacity-0"
                    x-transition:enter-end="scale-100 opacity-100"
                    x-transition:leave="transform transition ease-in duration-150"
                    x-transition:leave-start="scale-100 opacity-100"
                    x-transition:leave-end="scale-95 opacity-0"
                    class="modal-panel max-w-xl"
                >
                    <div class="modal-header flex items-center justify-between">
                        <div>
                            <h3 class="modal-title">Create Divisi</h3>
                            <p class="modal-subtitle">Tambahkan divisi baru ke sistem.</p>
                        </div>
                        <button type="button" @click="createOpen = false" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('divisions.store') }}" class="modal-form" data-submit-lock>
                        @csrf
                        <label class="block">
                            <span class="field-label">Nama Divisi</span>
                            <x-text-input name="name" placeholder="Contoh: Teknisi" value="{{ old('name') }}" class="form-control" required />
                        </label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                            Setelah divisi dibuat, klik baris divisi di tabel untuk mengatur alur tracker tiap tahap.
                        </div>

                        <div class="modal-footer">
                            <button type="button" @click="createOpen = false" class="btn-secondary-soft">Batal</button>
                            <x-primary-button class="btn-primary-soft normal-case tracking-normal">Simpan</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <section class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Table Divisi</h3>
                    <p class="mt-1 text-xs text-slate-500">Klik baris untuk melihat dan mengubah alur kerja divisi.</p>
                </div>

                <div class="app-table-wrap rounded-none border-0 shadow-none">
                    <table class="data-table admin-table-fixed admin-table-xl">
                        <colgroup>
                            <col class="w-[5%]">
                            <col class="w-[18%]">
                            <col class="w-[13%]">
                            <col class="w-[13%]">
                            <col class="w-[13%]">
                            <col class="w-[13%]">
                            <col class="w-[12%]">
                            <col class="w-[13%]">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Divisi</th>
                                <th>Step 1</th>
                                <th>Step 2</th>
                                <th>Step 3</th>
                                <th>Step 4</th>
                                <th>Syarat Aktif</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($divisions as $division)
                                @php
                                    $requirements = 0;
                                    for ($i = 1; $i <= 4; $i++) {
                                        $requirements += (int) $division->{"req_desc_$i"};
                                        $requirements += (int) $division->{"req_photo_$i"};
                                        $requirements += (int) $division->{"req_video_$i"};
                                    }
                                @endphp
                                <tr class="data-table-row cursor-pointer" @click="selected = {{ $division->id }}">
                                    <td class="text-slate-500">{{ $loop->iteration }}</td>
                                    <td class="min-w-52">
                                        <div class="font-semibold text-slate-950">{{ $division->name }}</div>
                                        <div class="mt-1 text-xs text-slate-500">Tracker 4 tahap</div>
                                    </td>
                                    @for($i = 1; $i <= 4; $i++)
                                        <td class="min-w-44 text-slate-700">
                                            {{ $division->{"step_$i"} ?: 'Belum diatur' }}
                                        </td>
                                    @endfor
                                    <td>
                                        <span class="app-badge-muted">{{ $requirements }} syarat</span>
                                    </td>
                                    <td class="text-right">
                                        <button type="button" @click.stop="selected = {{ $division->id }}" class="btn-action-edit">
                                            <i class="fas fa-pen"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="app-empty-state">
                                            <div class="app-empty-state-icon"><i class="fas fa-sitemap"></i></div>
                                            <p class="mt-3 font-semibold text-slate-900">Belum ada divisi</p>
                                            <p class="mt-1 text-sm text-slate-500">Klik Create Divisi untuk menambahkan data.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @foreach($divisions as $division)
                <div x-show="selected === {{ $division->id }}" x-cloak x-transition.opacity class="modal-backdrop">
                    <div
                        @click.outside="selected = null"
                        x-transition:enter="transform transition ease-out duration-200"
                        x-transition:enter-start="scale-95 opacity-0"
                        x-transition:enter-end="scale-100 opacity-100"
                        x-transition:leave="transform transition ease-in duration-150"
                        x-transition:leave-start="scale-100 opacity-100"
                        x-transition:leave-end="scale-95 opacity-0"
                        class="modal-panel max-w-6xl"
                    >
                        <div class="modal-header flex items-center justify-between">
                            <div>
                                <h3 class="modal-title">Detail Divisi</h3>
                                <p class="modal-subtitle">{{ $division->name }} · Atur 4 tahap tracker kerja.</p>
                            </div>
                            <button type="button" @click="selected = null" class="modal-close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('divisions.update', $division->id) }}" class="modal-form" data-submit-lock>
                            @csrf
                            @method('PUT')

                            <label class="block">
                                <span class="field-label">Nama Divisi</span>
                                <x-text-input name="name" value="{{ $division->name }}" class="form-control" required />
                            </label>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                @for($i = 1; $i <= 4; $i++)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="mb-4 flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-950">Tahap {{ $i }}</p>
                                                <p class="mt-1 text-xs text-slate-500">Nama tahap dan bukti wajib.</p>
                                            </div>
                                            <span class="app-badge-muted">Step {{ $i }}</span>
                                        </div>

                                        <label class="block">
                                            <span class="field-label">Nama Tahap</span>
                                            <x-text-input name="step_{{ $i }}" value="{{ $division->{"step_$i"} }}" class="form-control" placeholder="Contoh: Menuju Lokasi" />
                                        </label>

                                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                                <input type="checkbox" name="req_desc_{{ $i }}" @checked($division->{"req_desc_$i"})>
                                                Deskripsi
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                                <input type="checkbox" name="req_photo_{{ $i }}" @checked($division->{"req_photo_$i"})>
                                                Foto
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                                <input type="checkbox" name="req_video_{{ $i }}" @checked($division->{"req_video_$i"})>
                                                Video
                                            </label>
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <div class="modal-footer">
                                <button type="button" @click="selected = null" class="btn-secondary-soft">Batal</button>
                                <x-primary-button class="btn-primary-soft normal-case tracking-normal">
                                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
