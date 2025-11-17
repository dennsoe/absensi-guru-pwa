@extends('layouts.app')

@section('title', 'Manajemen Jadwal Mengajar')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Manajemen Jadwal Mengajar</h4>
                    <p class="text-muted mb-0">Kelola jadwal mengajar guru</p>
                </div>
                <a href="{{ route('kurikulum.jadwal.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Jadwal
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Guru</label>
                            <select name="guru_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Guru</option>
                                @foreach($guru_list as $g)
                                <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kelas</label>
                            <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Kelas</option>
                                @foreach($kelas_list as $k)
                                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hari</label>
                            <select name="hari" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Hari</option>
                                <option value="senin" {{ request('hari') === 'senin' ? 'selected' : '' }}>Senin</option>
                                <option value="selasa" {{ request('hari') === 'selasa' ? 'selected' : '' }}>Selasa</option>
                                <option value="rabu" {{ request('hari') === 'rabu' ? 'selected' : '' }}>Rabu</option>
                                <option value="kamis" {{ request('hari') === 'kamis' ? 'selected' : '' }}>Kamis</option>
                                <option value="jumat" {{ request('hari') === 'jumat' ? 'selected' : '' }}>Jumat</option>
                                <option value="sabtu" {{ request('hari') === 'sabtu' ? 'selected' : '' }}>Sabtu</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" class="form-control" value="{{ request('tahun_ajaran', '2025/2026') }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('kurikulum.jadwal.index') }}" class="btn btn-secondary w-100">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Table -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Daftar Jadwal</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Guru</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Ruangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal as $j)
                        <tr>
                            <td><span class="badge bg-primary">{{ ucfirst($j->hari) }}</span></td>
                            <td>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</td>
                            <td>
                                <strong>{{ $j->guru->nama }}</strong><br>
                                <small class="text-muted">{{ $j->guru->nip }}</small>
                            </td>
                            <td>{{ $j->kelas->nama_kelas }}</td>
                            <td>{{ $j->mataPelajaran->nama_mapel }}</td>
                            <td>{{ $j->ruangan ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $j->status === 'aktif' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($j->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('kurikulum.jadwal.edit', $j->id) }}" class="btn btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('kurikulum.jadwal.destroy', $j->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Tidak ada jadwal</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($jadwal->hasPages())
            <div class="mt-3">
                {{ $jadwal->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
