@extends('layouts.app')

@section('title', 'Laporan Absensi Per Guru')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <h2 class="mb-1">Laporan Absensi Per Guru</h2>
                        <p class="text-muted">Detail kehadiran {{ $guru->nama }}</p>
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

        <!-- Guru Info Card -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <x-user-avatar :user="$guru->user" size="xl" />
                        </div>
                        <h5 class="card-title">Informasi Guru</h5>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="120">Nama</td>
                                <td>: <strong>{{ $guru->nama }}</strong></td>
                            </tr>
                            <tr>
                                <td>NIP</td>
                                <td>: {{ $guru->nip }}</td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>: {{ $guru->email }}</td>
                            </tr>
                            <tr>
                                <td>No. HP</td>
                                <td>: {{ $guru->no_hp }}</td>
                            </tr>
                            <tr>
                                <td>Periode</td>
                                <td>:
                                    <strong>{{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y') }}</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Total</h6>
                                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="text-success mb-2">Hadir</h6>
                                <h3 class="mb-0 text-success">{{ $stats['hadir'] }}</h3>
                                <small
                                    class="text-muted">{{ $stats['total'] > 0 ? round(($stats['hadir'] / $stats['total']) * 100, 1) : 0 }}%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="text-warning mb-2">Terlambat</h6>
                                <h3 class="mb-0 text-warning">{{ $stats['terlambat'] }}</h3>
                                <small
                                    class="text-muted">{{ $stats['total'] > 0 ? round(($stats['terlambat'] / $stats['total']) * 100, 1) : 0 }}%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="text-info mb-2">Izin</h6>
                                <h3 class="mb-0 text-info">{{ $stats['izin'] }}</h3>
                                <small
                                    class="text-muted">{{ $stats['total'] > 0 ? round(($stats['izin'] / $stats['total']) * 100, 1) : 0 }}%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
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
            </div>
        </div>

        <!-- Period Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.laporan.per-guru') }}" class="row g-3">
                    <input type="hidden" name="guru_id" value="{{ $guru->id }}">
                    <div class="col-auto">
                        <label class="form-label">Pilih Periode</label>
                    </div>
                    <div class="col-auto">
                        <select name="bulan" class="form-select">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="tahun" class="form-select">
                            @for ($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Riwayat Absensi</h5>
            </div>
            <div class="card-body">
                @if ($absensi_list->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Hari</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Jam Mengajar</th>
                                    <th>Waktu Absen</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($absensi_list as $absensi)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('d M Y') }}</td>
                                        <td>{{ ucfirst($absensi->jadwal->hari) }}</td>
                                        <td>{{ $absensi->jadwal->kelas->nama_kelas }}</td>
                                        <td>{{ $absensi->jadwal->mataPelajaran->nama_mapel }}</td>
                                        <td>{{ $absensi->jadwal->jam_mulai }} - {{ $absensi->jadwal->jam_selesai }}</td>
                                        <td>{{ $absensi->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '-' }}
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
                                        <td>{{ $absensi->keterangan ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Belum ada data absensi untuk periode ini</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Summary Chart Placeholder -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Grafik Kehadiran</h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-bar-chart" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-2">Fitur grafik akan segera hadir</p>
                </div>
            </div>
        </div>
    </div>
@endsection
