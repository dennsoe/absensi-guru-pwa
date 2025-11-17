@extends('layouts.app')

@section('title', 'Kelola Users')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Kelola Users</h1>
            <p class="page-subtitle">Manajemen Akun Guru & Admin</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah User Baru
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="page-section">
        <form method="GET" action="{{ route('admin.users') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari nama, username, atau NIP..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-control">
                    <option value="">Semua Role</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="guru" {{ request('role') === 'guru' ? 'selected' : '' }}>Guru</option>
                    <option value="guru_piket" {{ request('role') === 'guru_piket' ? 'selected' : '' }}>Guru Piket</option>
                    <option value="kepala_sekolah" {{ request('role') === 'kepala_sekolah' ? 'selected' : '' }}>Kepala
                        Sekolah</option>
                    <option value="kurikulum" {{ request('role') === 'kurikulum' ? 'selected' : '' }}>Kurikulum</option>
                    <option value="ketua_kelas" {{ request('role') === 'ketua_kelas' ? 'selected' : '' }}>Ketua Kelas
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="data-table-container">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>NIP</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>{{ $user->username }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <x-user-avatar :user="$user" size="sm" class="me-2" />
                                    <span>{{ $user->nama }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email ?? '-' }}</td>
                            <td>{{ $user->nip ?? '-' }}</td>
                            <td>
                                @if ($user->role === 'admin')
                                    <span class="badge badge-danger">Admin</span>
                                @elseif($user->role === 'guru')
                                    <span class="badge badge-primary">Guru</span>
                                @elseif($user->role === 'guru_piket')
                                    <span class="badge badge-warning">Guru Piket</span>
                                @elseif($user->role === 'kepala_sekolah')
                                    <span class="badge badge-success">Kepala Sekolah</span>
                                @elseif($user->role === 'kurikulum')
                                    <span class="badge badge-primary">Kurikulum</span>
                                @elseif($user->role === 'ketua_kelas')
                                    <span class="badge badge-gray">Ketua Kelas</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if ($user->id !== Auth::id())
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete({{ $user->id }})" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $user->id }}"
                                            action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-people empty-state-icon"></i>
                                    <div class="empty-state-title">Tidak ada data user</div>
                                    <div class="empty-state-message">
                                        @if (request('search') || request('role'))
                                            Tidak ada user yang sesuai dengan filter
                                        @else
                                            Belum ada user yang terdaftar
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Menampilkan {{ $users->firstItem() }} sampai {{ $users->lastItem() }} dari {{ $users->total() }} data
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelete(userId) {
            if (confirm('Apakah Anda yakin ingin menghapus user ini? Data yang terkait juga akan terhapus.')) {
                document.getElementById('delete-form-' + userId).submit();
            }
        }
    </script>
@endpush
