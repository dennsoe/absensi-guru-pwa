@extends('layouts.app')

@section('title', 'Data Mata Pelajaran')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Data Mata Pelajaran</h1>
            <p class="page-subtitle">Manajemen Mata Pelajaran Sekolah</p>
        </div>
        <a href="{{ route('admin.mapel.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Mata Pelajaran
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="page-section">
        <form method="GET" action="{{ route('admin.mapel.index') }}" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control"
                    placeholder="Cari kode atau nama mata pelajaran..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.mapel.index') }}" class="btn btn-outline-secondary w-100">
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
                        <th>Kode</th>
                        <th>Nama Mata Pelajaran</th>
                        <th>Deskripsi</th>
                        <th>Digunakan di</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mapel_list as $index => $mapel)
                        <tr>
                            <td>{{ $mapel_list->firstItem() + $index }}</td>
                            <td><span class="badge badge-primary">{{ $mapel->kode_mapel }}</span></td>
                            <td><strong>{{ $mapel->nama_mapel }}</strong></td>
                            <td>{{ Str::limit($mapel->deskripsi ?? '-', 50) }}</td>
                            <td>{{ $mapel->jadwal_mengajar_count }} jadwal</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.mapel.edit', $mapel->id) }}" class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if ($mapel->jadwal_mengajar_count == 0)
                                        <form action="{{ route('admin.mapel.destroy', $mapel->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus mata pelajaran {{ $mapel->nama_mapel }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-danger"
                                            title="Tidak dapat dihapus (digunakan dalam jadwal)" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada data mata pelajaran</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($mapel_list->hasPages())
            <div class="pagination-wrapper">
                {{ $mapel_list->links() }}
            </div>
        @endif
    </div>
@endsection
