<x-app-layout>
    <div
        class="admin-shell"
        x-data="{
            createOpen: false,
            cacheKey: 'admin.clients.create.draft',
            hasServerOld: @js(session()->hasOldInput()),
            createForm: {
                name: @js(old('name', '')),
                contact_person: @js(old('contact_person', '')),
                phone: @js(old('phone', '')),
                email: @js(old('email', '')),
                project_name: @js(old('project_name', '')),
                status: @js(old('status', 'Prospective')),
                address: @js(old('address', '')),
                notes: @js(old('notes', '')),
            },
            init() {
                if (!this.hasServerOld) {
                    const draft = localStorage.getItem(this.cacheKey);
                    if (draft) {
                        try {
                            this.createForm = { ...this.createForm, ...JSON.parse(draft) };
                        } catch (error) {
                            localStorage.removeItem(this.cacheKey);
                        }
                    }
                }

                this.$watch('createForm', value => {
                    localStorage.setItem(this.cacheKey, JSON.stringify(value));
                }, { deep: true });
            },
            clearCreateCache() {
                localStorage.removeItem(this.cacheKey);
            }
        }"
        @keydown.escape.window="createOpen = false"
    >
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="admin-title">Data Client</h2>
                            <p class="admin-subtitle">Daftar client, kontak, project, dan dokumen PDF.</p>
                        </div>

                        <button type="button" @click="createOpen = true" class="btn-primary-soft">
                            <i class="fas fa-plus mr-2"></i> Add Client
                        </button>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert-success">
                    {{ session('success') }}
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

            <div x-show="createOpen" x-cloak x-transition.opacity class="modal-backdrop">
                <div
                    @click.outside="createOpen = false"
                    x-transition:enter="transform transition ease-out duration-200"
                    x-transition:enter-start="scale-95 opacity-0"
                    x-transition:enter-end="scale-100 opacity-100"
                    x-transition:leave="transform transition ease-in duration-150"
                    x-transition:leave-start="scale-100 opacity-100"
                    x-transition:leave-end="scale-95 opacity-0"
                    class="modal-panel"
                >
                    <div class="modal-header">
                        <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="modal-title">Add Client</h3>
                            <p class="modal-subtitle">Masukkan data client baru.</p>
                        </div>
                        <button type="button" @click="createOpen = false" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                        </div>
                        <div class="mt-4 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
                            Isian disimpan sementara sampai data berhasil dikirim.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.clients.store') }}" enctype="multipart/form-data" class="modal-form" @submit="clearCreateCache()">
                        @csrf
                        <div class="grid grid-cols-1 gap-4">
                            <label>
                                <span class="field-label">Nama Client</span>
                                <x-text-input name="name" placeholder="Nama client" x-model="createForm.name" class="form-control" required />
                            </label>
                            <label>
                                <span class="field-label">Kontak / PIC</span>
                                <x-text-input name="contact_person" placeholder="Kontak / PIC" x-model="createForm.contact_person" class="form-control" />
                            </label>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label>
                                    <span class="field-label">Telepon</span>
                                    <x-text-input name="phone" placeholder="No. telepon" x-model="createForm.phone" class="form-control" />
                                </label>
                                <label>
                                    <span class="field-label">Email</span>
                                    <x-text-input name="email" type="email" placeholder="Email" x-model="createForm.email" class="form-control" />
                                </label>
                            </div>
                            <label>
                                <span class="field-label">Project</span>
                                <x-text-input name="project_name" placeholder="Project" x-model="createForm.project_name" class="form-control" />
                            </label>
                            <label>
                                <span class="field-label">Status</span>
                            <select name="status" x-model="createForm.status" class="form-control" required>
                                @foreach(['Active' => 'Active', 'Prospective' => 'Prospective', 'Inactive' => 'Inactive'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            </label>
                        </div>

                        <label class="block">
                            <span class="field-label">Alamat</span>
                            <textarea name="address" rows="3" placeholder="Alamat" x-model="createForm.address" class="form-control"></textarea>
                        </label>
                        <label class="block">
                            <span class="field-label">Catatan</span>
                            <textarea name="notes" rows="3" placeholder="Catatan" x-model="createForm.notes" class="form-control"></textarea>
                        </label>

                        <div class="upload-box">
                            <label class="field-label">PDF</label>
                            <input type="file" name="document" accept="application/pdf,.pdf" class="block w-full rounded-lg border border-slate-300 text-sm text-slate-700 file:mr-4 file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>

                        <div class="modal-footer">
                            <button type="button" @click="createOpen = false" class="btn-secondary-soft">Batal</button>
                            <x-primary-button class="btn-primary-soft normal-case tracking-normal">Simpan</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Table Client</h3>
                </div>
                <div class="app-table-wrap rounded-none border-0 shadow-none">
                    <table class="data-table admin-table-2xl">
                        <colgroup>
                            <col class="w-[5%]">
                            <col class="w-[20%]">
                            <col class="w-[12%]">
                            <col class="w-[11%]">
                            <col class="w-[16%]">
                            <col class="w-[14%]">
                            <col class="w-[9%]">
                            <col class="w-[6%]">
                            <col class="w-[7%]">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Client</th>
                                <th>Kontak</th>
                                <th>Telepon</th>
                                <th>Email</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>PDF</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clients as $client)
                                <tr class="data-table-row" x-data="{ editOpen: false }" @keydown.escape.window="editOpen = false">
                                    <td class="text-slate-500">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $client->name }}</div>
                                        <div class="mt-1 max-w-xs text-xs leading-5 text-slate-500">{{ $client->address ?: '-' }}</div>
                                    </td>
                                    <td class="text-slate-700">{{ $client->contact_person ?: '-' }}</td>
                                    <td class="text-slate-700">{{ $client->phone ?: '-' }}</td>
                                    <td class="text-slate-700">{{ $client->email ?: '-' }}</td>
                                    <td class="text-slate-700">{{ $client->project_name ?: '-' }}</td>
                                    <td>
                                        <span class="status-badge {{ $client->status === 'Active' ? 'status-badge-active' : ($client->status === 'Inactive' ? 'status-badge-inactive' : 'status-badge-prospective') }}">
                                            {{ $client->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($client->document_url)
                                            <a href="{{ $client->document_url }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <button type="button" @click="editOpen = true" class="btn-action-edit">
                                                <i class="fas fa-pen"></i> Edit
                                            </button>
                                            <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus client {{ $client->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action-delete">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>

                                        <div x-show="editOpen" x-cloak x-transition.opacity class="modal-backdrop">
                                            <div
                                                @click.outside="editOpen = false"
                                                x-transition:enter="transform transition ease-out duration-200"
                                                x-transition:enter-start="scale-95 opacity-0"
                                                x-transition:enter-end="scale-100 opacity-100"
                                                x-transition:leave="transform transition ease-in duration-150"
                                                x-transition:leave-start="scale-100 opacity-100"
                                                x-transition:leave-end="scale-95 opacity-0"
                                                class="modal-panel"
                                            >
                                                <div class="modal-header flex items-center justify-between">
                                                    <div>
                                                        <h3 class="modal-title">Edit Client</h3>
                                                        <p class="modal-subtitle">{{ $client->name }}</p>
                                                    </div>
                                                    <button type="button" @click="editOpen = false" class="modal-close">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>

                                                <form method="POST" action="{{ route('admin.clients.update', $client) }}" enctype="multipart/form-data" class="modal-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="grid grid-cols-1 gap-4">
                                                        <label>
                                                            <span class="field-label">Nama Client</span>
                                                            <x-text-input name="name" value="{{ $client->name }}" class="form-control" required />
                                                        </label>
                                                        <label>
                                                            <span class="field-label">Kontak / PIC</span>
                                                            <x-text-input name="contact_person" value="{{ $client->contact_person }}" class="form-control" />
                                                        </label>
                                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                            <label>
                                                                <span class="field-label">Telepon</span>
                                                                <x-text-input name="phone" value="{{ $client->phone }}" class="form-control" />
                                                            </label>
                                                            <label>
                                                                <span class="field-label">Email</span>
                                                                <x-text-input name="email" type="email" value="{{ $client->email }}" class="form-control" />
                                                            </label>
                                                        </div>
                                                        <label>
                                                            <span class="field-label">Project</span>
                                                            <x-text-input name="project_name" value="{{ $client->project_name }}" class="form-control" />
                                                        </label>
                                                        <label>
                                                            <span class="field-label">Status</span>
                                                        <select name="status" class="form-control" required>
                                                            @foreach(['Active' => 'Active', 'Prospective' => 'Prospective', 'Inactive' => 'Inactive'] as $value => $label)
                                                                <option value="{{ $value }}" @selected($client->status === $value)>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                        </label>
                                                    </div>

                                                    <label class="block">
                                                        <span class="field-label">Alamat</span>
                                                        <textarea name="address" rows="3" class="form-control">{{ $client->address }}</textarea>
                                                    </label>
                                                    <label class="block">
                                                        <span class="field-label">Catatan</span>
                                                        <textarea name="notes" rows="3" class="form-control">{{ $client->notes }}</textarea>
                                                    </label>

                                                    <div class="upload-box">
                                                        <label class="field-label">Ganti PDF</label>
                                                        <input type="file" name="document" accept="application/pdf,.pdf" class="block w-full rounded-lg border border-slate-300 text-sm text-slate-700 file:mr-4 file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" @click="editOpen = false" class="btn-secondary-soft">Batal</button>
                                                        <x-primary-button class="btn-primary-soft normal-case tracking-normal">Simpan</x-primary-button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="app-empty-state">
                                            <div class="app-empty-state-icon"><i class="fas fa-building"></i></div>
                                            <p class="mt-3 font-semibold text-slate-900">Belum ada data client</p>
                                            <p class="mt-1 text-sm text-slate-500">Klik Add Client untuk menambahkan data pertama.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
