@extends('layouts.app')

@section('title', 'Jadwal Mengajar Saya')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h4 class="mb-1">Jadwal Mengajar Saya</h4>
                <p class="text-muted mb-0">Lihat jadwal mengajar pribadi Anda</p>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $total_jam_perminggu }}</h3>
                        <p class="text-muted mb-0 small">Total Jam/Minggu</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $total_kelas }}</h3>
                        <p class="text-muted mb-0 small">Total Kelas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $total_mapel }}</h3>
                        <p class="text-muted mb-0 small">Mata Pelajaran</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Filter Hari</label>
                                <select name="hari" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua Hari</option>
                                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $h)
                                        <option value="{{ strtolower($h) }}" {{ request('hari') === strtolower($h) ? 'selected' : '' }}>
                                            {{ $h }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tahun Ajaran</label>
                                <input type="text" name="tahun_ajaran" class="form-control"
                                    value="{{ request('tahun_ajaran', '2025/2026') }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Semester</label>
                                <select name="semester" class="form-select" onchange="this.form.submit()">
                                    <option value="Ganjil" {{ request('semester', 'Ganjil') == 'Ganjil' ? 'selected' : '' }}>Semester Ganjil</option>
                                    <option value="Genap" {{ request('semester') == 'Genap' ? 'selected' : '' }}>Semester Genap</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadwal Grouped by Day -->
        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
            @if ($jadwal_grouped->has($hari))
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-day"></i> {{ $hari }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Jam</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Ruangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($jadwal_grouped[$hari] as $j)
                                        <tr>
                                            <td><strong>{{ substr($j->jam_mulai, 0, 5) }} -
                                                    {{ substr($j->jam_selesai, 0, 5) }}</strong></td>
                                            <td>{{ $j->kelas->nama_kelas }}</td>
                                            <td>{{ $j->mataPelajaran->nama_mapel }}</td>
                                            <td>{{ $j->ruangan ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('guru.jadwal.show', $j->id) }}"
                                                    class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        @if ($jadwal->isEmpty())
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i> Tidak ada jadwal mengajar
            </div>
        @endif
    </div>
@endsection
