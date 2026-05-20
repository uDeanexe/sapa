<x-app-layout>
    @php
        $defaultPasswordCount = $users->where('is_default_password', true)->count();
        $safePasswordCount = $users->where('is_default_password', false)->count();
    @endphp

    <div
        class="admin-shell"
        x-data="{ createOpen: @js($errors->any()) }"
        @keydown.escape.window="createOpen = false"
    >
        <div class="admin-container">
            <div class="admin-page-header">
                <div class="admin-page-header-accent"></div>
                <div class="admin-page-header-body">
                    <div>
                        <h2 class="admin-title">Manajemen Karyawan</h2>
                        <p class="admin-subtitle">Tambah karyawan, atur divisi, dan pantau status password akun.</p>
                    </div>
                    <button type="button" class="btn-primary-soft" @click="createOpen = true">
                        <i class="fas fa-user-plus mr-2"></i>
                        Tambah Karyawan
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div id="user-success" class="alert-success flex items-center justify-between gap-4">
                    <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                    <button type="button" data-dismiss="#user-success" class="text-emerald-700 hover:text-emerald-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div id="user-error" class="alert-error flex items-center justify-between gap-4">
                    <span><i class="fas fa-circle-exclamation mr-2"></i>{{ session('error') }}</span>
                    <button type="button" data-dismiss="#user-error" class="text-rose-700 hover:text-rose-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    <p class="font-semibold">Data belum bisa disimpan.</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="metric-card metric-sky">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-500">Total Karyawan</p>
                        <span class="metric-icon text-sky-600">
                            <i class="fas fa-users"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ $users->count() }}</p>
                    <p class="mt-1 text-xs text-slate-500">Akun karyawan terdaftar</p>
                </div>
                <div class="metric-card metric-rose">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-500">Wajib Ganti Password</p>
                        <span class="metric-icon text-rose-600">
                            <i class="fas fa-key"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-rose-700">{{ $defaultPasswordCount }}</p>
                    <p class="mt-1 text-xs text-slate-500">Masih memakai password awal</p>
                </div>
                <div class="metric-card metric-emerald">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-500">Password Aman</p>
                        <span class="metric-icon text-emerald-600">
                            <i class="fas fa-shield-halved"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-emerald-700">{{ $safePasswordCount }}</p>
                    <p class="mt-1 text-xs text-slate-500">Sudah tidak memakai default</p>
                </div>
            </section>

            <section class="admin-card">
                <div class="admin-card-header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="admin-card-title">Daftar Karyawan</h3>
                        <p class="mt-1 text-xs text-slate-500">Reset password hanya jika karyawan lupa atau belum mengganti password awal.</p>
                    </div>
                    <div class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600">
                        {{ $users->count() }} data
                    </div>
                </div>

                <div class="app-table-wrap rounded-none border-0 shadow-none">
                    <table class="data-table admin-table-fixed">
                        <colgroup>
                            <col class="w-[25%]">
                            <col class="w-[27%]">
                            <col class="w-[18%]">
                            <col class="w-[15%]">
                            <col class="w-[15%]">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Divisi</th>
                                <th>Status Password</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="data-table-row">
                                    <td class="min-w-56">
                                        <p class="font-semibold text-slate-950">{{ $user->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Karyawan</p>
                                    </td>
                                    <td class="min-w-60 text-slate-600">{{ $user->email }}</td>
                                    <td>
                                        <span class="app-badge-muted">{{ $user->division->name ?? 'Belum Set' }}</span>
                                    </td>
                                    <td>
                                        <span class="{{ $user->is_default_password ? 'app-badge bg-rose-100 text-rose-700' : 'app-badge-success' }}">
                                            {{ $user->is_default_password ? 'Wajib Ganti' : 'Sudah Aman' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <form action="{{ route('users-management.reset-password', $user->id) }}" method="POST" data-confirm="Reset password {{ $user->name }} ke jonusa123?">
                                                @csrf
                                                <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                                    <i class="fas fa-rotate-left"></i>
                                                    Reset
                                                </button>
                                            </form>
                                            <form action="{{ route('users-management.destroy', $user->id) }}" method="POST" data-confirm="Yakin ingin menghapus karyawan ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                                    <i class="fas fa-trash"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="app-empty-state">
                                            <div class="app-empty-state-icon"><i class="fas fa-user-group"></i></div>
                                            <p class="mt-3 font-semibold text-slate-900">Belum ada karyawan terdaftar</p>
                                            <p class="mt-1 text-sm text-slate-500">Tambah karyawan untuk mulai mengelola akun dan divisi.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div
                x-show="createOpen"
                x-cloak
                x-transition.opacity
                class="modal-backdrop"
                role="dialog"
                aria-modal="true"
                aria-labelledby="create-user-modal-title"
                @click.self="createOpen = false"
            >
                <div
                    class="modal-panel max-w-2xl"
                    x-transition:enter="transform transition ease-out duration-200"
                    x-transition:enter-start="scale-95 opacity-0"
                    x-transition:enter-end="scale-100 opacity-100"
                    x-transition:leave="transform transition ease-in duration-150"
                    x-transition:leave-start="scale-100 opacity-100"
                    x-transition:leave-end="scale-95 opacity-0"
                >
                    <div class="modal-header">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 id="create-user-modal-title" class="modal-title">Tambah Karyawan Baru</h3>
                                <p class="modal-subtitle">Password default otomatis diset ke jonusa123.</p>
                            </div>
                            <button type="button" class="modal-close" @click="createOpen = false" aria-label="Tutup popup">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('users-management.store') }}" class="modal-body space-y-4" data-submit-lock>
                        @csrf
                        @if($errors->any())
                            <div class="alert-error">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <label>
                            <span class="field-label">Nama Lengkap</span>
                            <x-text-input name="name" value="{{ old('name') }}" placeholder="Nama karyawan" class="form-control" required />
                        </label>
                        <label>
                            <span class="field-label">Email Kantor</span>
                            <x-text-input name="email" type="email" value="{{ old('email') }}" placeholder="nama@kantor.com" class="form-control" required />
                        </label>
                        <label>
                            <span class="field-label">Divisi</span>
                            <select name="division_id" class="form-control" required>
                                <option value="">Pilih divisi</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" @selected(old('division_id') == $division->id)>{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                            Setelah akun dibuat, karyawan wajib mengganti password default saat login.
                        </div>

                        <div class="modal-footer -mx-6 -mb-6 mt-6">
                            <button type="button" class="btn-secondary-soft" @click="createOpen = false">Batal</button>
                            <button type="submit" class="btn-primary-soft">
                                <i class="fas fa-user-plus mr-2"></i>
                                Simpan Karyawan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
