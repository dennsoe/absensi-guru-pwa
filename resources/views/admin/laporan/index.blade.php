@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Laporan Absensi</h2>
                        <p class="text-muted">Monitor dan analisis data kehadiran guru</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2" onclick="alert('Fitur Export PDF akan segera hadir')">
                            <i class="bi bi-file-pdf"></i> Export PDF
                        </button>
                        <button class="btn btn-outline-success" onclick="alert('Fitur Export Excel akan segera hadir')">
                            <i class="bi bi-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Laporan</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.laporan.index') }}">
                    <div class="row g-3">
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
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat
                                </option>
                                <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ request('status') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select">
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}"
                                        {{ request('bulan', now()->month) == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select">
                                @for ($y = now()->year; $y >= now()->year - 3; $y--)
                                    <option value="{{ $y }}"
                                        {{ request('tahun', now()->year) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Tampilkan
                            </button>
                            <a href="{{ route('admin.laporan.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Absensi</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-success mb-2">Hadir</h6>
                        <h3 class="mb-0 text-success">{{ $stats['hadir'] }}</h3>
                        <small
                            class="text-muted">{{ $stats['total'] > 0 ? round(($stats['hadir'] / $stats['total']) * 100, 1) : 0 }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-warning mb-2">Terlambat</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['terlambat'] }}</h3>
                        <small
                            class="text-muted">{{ $stats['total'] > 0 ? round(($stats['terlambat'] / $stats['total']) * 100, 1) : 0 }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-info mb-2">Izin</h6>
                        <h3 class="mb-0 text-info">{{ $stats['izin'] }}</h3>
                        <small
                            class="text-muted">{{ $stats['total'] > 0 ? round(($stats['izin'] / $stats['total']) * 100, 1) : 0 }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="text-danger mb-2">Alpha</h6>
                        <h3 class="mb-0 text-danger">{{ $stats['alpha'] }}</h3>
                        <small
                            class="text-muted">{{ $stats['total'] > 0 ? round(($stats['alpha'] / $stats['total']) * 100, 1) : 0 }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Data Absensi</h5>
            </div>
            <div class="card-body">
                @if ($absensi_list->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Guru</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Hari</th>
                                    <th>Jam</th>
                                    <th>Status</th>
                                    <th>Waktu Absen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($absensi_list as $absensi)
                                    <tr>
                                        <td>{{ $absensi_list->firstItem() + $loop->index }}</td>
                                        <td>{{ \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('d M Y') }}</td>
                                        <td>{{ $absensi->guru->nama }}</td>
                                        <td>{{ $absensi->jadwal->kelas->nama_kelas }}</td>
                                        <td>{{ $absensi->jadwal->mataPelajaran->nama_mapel }}</td>
                                        <td>{{ ucfirst($absensi->jadwal->hari) }}</td>
                                        <td>{{ $absensi->jadwal->jam_mulai }} - {{ $absensi->jadwal->jam_selesai }}</td>
                                        <td>
                                            @if ($absensi->status_kehadiran == 'hadir')
                                                <span class="badge bg-success">Hadir</span>
                                            @elseif($absensi->status_kehadiran == 'terlambat')
                                                <span class="badge bg-warning">Terlambat</span>
                                            @elseif(
                                                $absensi->status_kehadiran == 'izin' ||
                                                    $absensi->status_kehadiran == 'sakit' ||
                                                    $absensi->status_kehadiran == 'cuti' ||
                                                    $absensi->status_kehadiran == 'dinas')
                                                <span class="badge bg-info">Izin</span>
                                            @else
                                                <span class="badge bg-danger">Alpha</span>
                                            @endif
                                        </td>
                                        <td>{{ $absensi->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $absensi_list->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Tidak ada data absensi untuk filter yang dipilih</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-badge"></i> Laporan Per Guru</h5>
                        <p class="card-text text-muted">Lihat detail absensi untuk guru tertentu</p>
                        <form method="GET" action="{{ route('admin.laporan.per-guru') }}" class="d-flex gap-2">
                            <select name="guru_id" class="form-select" required>
                                <option value="">Pilih Guru</option>
                                @foreach ($guru_list as $guru)
                                    <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                                @endforeach
                            </select>
                            <select name="bulan" class="form-select" required>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ now()->month == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                            <select name="tahun" class="form-select" required>
                                @for ($y = now()->year; $y >= now()->year - 3; $y--)
                                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                            <button type="submit" class="btn btn-primary">Lihat</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-building"></i> Laporan Per Kelas</h5>
                        <p class="card-text text-muted">Lihat detail absensi untuk kelas tertentu</p>
                        <form method="GET" action="{{ route('admin.laporan.per-kelas') }}" class="d-flex gap-2">
                            <select name="kelas_id" class="form-select" required>
                                <option value="">Pilih Kelas</option>
                                @foreach ($kelas_list as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                @endforeach
                            </select>
                            <select name="bulan" class="form-select" required>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ now()->month == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                            <select name="tahun" class="form-select" required>
                                @for ($y = now()->year; $y >= now()->year - 3; $y--)
                                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                            <button type="submit" class="btn btn-primary">Lihat</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .col-md-2-4 {
            flex: 0 0 auto;
            width: 20%;
        }

        @media (max-width: 768px) {
            .col-md-2-4 {
                width: 100%;
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection
