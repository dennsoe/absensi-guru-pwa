@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Data Kelas</h1>
            <p class="page-subtitle">Manajemen Data Kelas Sekolah</p>
        </div>
        <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Kelas Baru
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="page-section">
        <form method="GET" action="{{ route('admin.kelas.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari nama kelas atau jurusan..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="tingkat" class="form-control">
                    <option value="">Semua Tingkat</option>
                    <option value="10" {{ request('tingkat') === '10' ? 'selected' : '' }}>Kelas 10</option>
                    <option value="11" {{ request('tingkat') === '11' ? 'selected' : '' }}>Kelas 11</option>
                    <option value="12" {{ request('tingkat') === '12' ? 'selected' : '' }}>Kelas 12</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="tahun_ajaran" class="form-control">
                    <option value="">Semua Tahun</option>
                    <option value="2024/2025" {{ request('tahun_ajaran') === '2024/2025' ? 'selected' : '' }}>2024/2025
                    </option>
                    <option value="2025/2026" {{ request('tahun_ajaran') === '2025/2026' ? 'selected' : '' }}>2025/2026
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary w-100">
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
                        <th>Nama Kelas</th>
                        <th>Tingkat</th>
                        <th>Jurusan</th>
                        <th>Wali Kelas</th>
                        <th>Ketua Kelas</th>
                        <th>Tahun Ajaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas_list as $index => $kelas)
                        <tr>
                            <td>{{ $kelas_list->firstItem() + $index }}</td>
                            <td><strong>{{ $kelas->nama_kelas }}</strong></td>
                            <td>{{ $kelas->tingkat }}</td>
                            <td>{{ $kelas->jurusan ?? '-' }}</td>
                            <td>{{ $kelas->waliKelas->nama ?? '-' }}</td>
                            <td>{{ $kelas->ketuaKelas->nama ?? '-' }}</td>
                            <td>{{ $kelas->tahun_ajaran }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.kelas.edit', $kelas->id) }}" class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.kelas.destroy', $kelas->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus kelas {{ $kelas->nama_kelas }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada data kelas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($kelas_list->hasPages())
            <div class="pagination-wrapper">
                {{ $kelas_list->links() }}
            </div>
        @endif
    </div>
@endsection
