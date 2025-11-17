@extends('layouts.app')

@section('title', 'Jadwal Mengajar')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Jadwal Mengajar</h1>
            <p class="page-subtitle">Manajemen Jadwal Mengajar Guru</p>
        </div>
        <a href="{{ route('admin.jadwal.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Jadwal Baru
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="page-section">
        <form method="GET" action="{{ route('admin.jadwal.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="guru_id" class="form-control">
                    <option value="">Semua Guru</option>
                    @foreach ($guru_list as $guru)
                        <option value="{{ $guru->id }}" {{ request('guru_id') == $guru->id ? 'selected' : '' }}>
                            {{ $guru->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="kelas_id" class="form-control">
                    <option value="">Semua Kelas</option>
                    @foreach ($kelas_list as $kelas)
                        <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="hari" class="form-control">
                    <option value="">Semua Hari</option>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                        <option value="{{ $hari }}" {{ request('hari') === $hari ? 'selected' : '' }}>
                            {{ $hari }}</option>
                    @endforeach
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
            <div class="col-md-1">
                <select name="status" class="form-control">
                    <option value="">Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i>
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
                        <th>Guru</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Hari</th>
                        <th>Waktu</th>
                        <th>Ruangan</th>
                        <th>Tahun/Semester</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwal_list as $index => $jadwal)
                        <tr>
                            <td>{{ $jadwal_list->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <x-user-avatar :user="$jadwal->guru->user" size="sm" class="me-2" />
                                    <span>{{ $jadwal->guru->nama }}</span>
                                </div>
                            </td>
                            <td><strong>{{ $jadwal->kelas->nama_kelas }}</strong></td>
                            <td>{{ $jadwal->mataPelajaran->nama_mapel }}</td>
                            <td>{{ $jadwal->hari }}</td>
                            <td>{{ date('H:i', strtotime($jadwal->jam_mulai)) }} -
                                {{ date('H:i', strtotime($jadwal->jam_selesai)) }}</td>
                            <td>{{ $jadwal->ruangan ?? '-' }}</td>
                            <td>{{ $jadwal->tahun_ajaran }}<br><small>{{ $jadwal->semester }}</small></td>
                            <td>
                                @if ($jadwal->status === 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.jadwal.edit', $jadwal->id) }}" class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.jadwal.destroy', $jadwal->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
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
                            <td colspan="10" class="text-center">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Tidak ada data jadwal</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($jadwal_list->hasPages())
            <div class="pagination-wrapper">
                {{ $jadwal_list->links() }}
            </div>
        @endif
    </div>
@endsection
