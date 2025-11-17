@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Rekap Absensi</h2>
                        <p class="text-muted">Monitoring kehadiran guru per hari</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-file-text"></i> Laporan Lengkap
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.absensi.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control"
                                value="{{ request('tanggal', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Guru</label>
                            <select name="guru_id" class="form-select">
                                <option value="">Semua Guru</option>
                                @foreach ($guru_list as $guru)
                                    <option value="{{ $guru->id }}"
                                        {{ request('guru_id') == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kelas</label>
                            <select name="kelas_id" class="form-select">
                                <option value="">Semua Kelas</option>
                                @foreach ($kelas_list as $kelas)
                                    <option value="{{ $kelas->id }}"
                                        {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                        {{ $kelas->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>
                                    Terlambat</option>
                                <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ request('status') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Tampilkan
                        </button>
                        <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Total Jadwal</h6>
                        <h3 class="mb-0">{{ $stats['total_jadwal'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6 class="mb-2">Sudah Absen</h6>
                        <h3 class="mb-0">{{ $stats['total_absen'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6 class="mb-2">Hadir</h6>
                        <h3 class="mb-0">{{ $stats['hadir'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h6 class="mb-2">Terlambat</h6>
                        <h3 class="mb-0">{{ $stats['terlambat'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6 class="mb-2">Izin</h6>
                        <h3 class="mb-0">{{ $stats['izin'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h6 class="mb-2">Alpha</h6>
                        <h3 class="mb-0">{{ $stats['alpha'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-check"></i> Data Absensi -
                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                </h5>
            </div>
            <div class="card-body">
                @if ($absensi_list->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Guru</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Jam</th>
                                    <th>Waktu Absen</th>
                                    <th>Status</th>
                                    <th>Metode</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($absensi_list as $index => $absensi)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $absensi->guru->nama }}</strong><br>
                                            <small class="text-muted">{{ $absensi->guru->nip ?? '-' }}</small>
                                        </td>
                                        <td>{{ $absensi->jadwal->kelas->nama_kelas }}</td>
                                        <td>{{ $absensi->jadwal->mataPelajaran->nama_mapel }}</td>
                                        <td>{{ $absensi->jadwal->jam_mulai }} - {{ $absensi->jadwal->jam_selesai }}</td>
                                        <td>
                                            @if ($absensi->jam_masuk)
                                                {{ \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($absensi->status_kehadiran == 'hadir')
                                                <span class="badge bg-success">Hadir</span>
                                            @elseif($absensi->status_kehadiran == 'terlambat')
                                                <span class="badge bg-warning">Terlambat</span>
                                            @elseif(in_array($absensi->status_kehadiran, ['izin', 'sakit', 'cuti', 'dinas']))
                                                <span
                                                    class="badge bg-info">{{ ucfirst($absensi->status_kehadiran) }}</span>
                                            @else
                                                <span class="badge bg-danger">Alpha</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-secondary">{{ ucfirst($absensi->metode ?? 'Manual') }}</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info"
                                                onclick="showDetail({{ $absensi->id }})" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Tidak ada data absensi untuk filter yang dipilih</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showDetail(id) {
                alert('Fitur detail absensi akan segera ditambahkan.\nAbsensi ID: ' + id);
            }
        </script>
    @endpush
@endsection
