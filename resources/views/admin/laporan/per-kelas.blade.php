@extends('layouts.app')

@section('title', 'Laporan Absensi Per Kelas')

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
                        <h2 class="mb-1">Laporan Absensi Per Kelas</h2>
                        <p class="text-muted">Detail kehadiran guru di {{ $kelas->nama_kelas }}</p>
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

        <!-- Kelas Info Card -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Informasi Kelas</h5>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="140">Nama Kelas</td>
                                <td>: <strong>{{ $kelas->nama_kelas }}</strong></td>
                            </tr>
                            <tr>
                                <td>Tingkat</td>
                                <td>: {{ $kelas->tingkat }}</td>
                            </tr>
                            <tr>
                                <td>Jurusan</td>
                                <td>: {{ $kelas->jurusan }}</td>
                            </tr>
                            <tr>
                                <td>Wali Kelas</td>
                                <td>: {{ $kelas->waliKelas->nama }}</td>
                            </tr>
                            <tr>
                                <td>Tahun Ajaran</td>
                                <td>: {{ $kelas->tahun_ajaran }}</td>
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
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Statistik Kehadiran Guru</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Guru</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Hadir</th>
                                        <th class="text-center">Terlambat</th>
                                        <th class="text-center">Izin</th>
                                        <th class="text-center">Alpha</th>
                                        <th class="text-center">Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($by_guru as $guru_id => $data)
                                        @php
                                            $total = $data['total'];
                                            $hadir = $data['hadir'];
                                            $terlambat = $data['terlambat'];
                                            $izin = $data['izin'];
                                            $alpha = $data['alpha'];
                                            $persentase = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $data['guru']->nama }}</strong></td>
                                            <td class="text-center">{{ $total }}</td>
                                            <td class="text-center text-success"><strong>{{ $hadir }}</strong></td>
                                            <td class="text-center text-warning">{{ $terlambat }}</td>
                                            <td class="text-center text-info">{{ $izin }}</td>
                                            <td class="text-center text-danger">{{ $alpha }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ $persentase >= 80 ? 'bg-success' : ($persentase >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                                    {{ $persentase }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Period Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.laporan.per-kelas') }}" class="row g-3">
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
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

        <!-- Detail by Guru -->
        @foreach ($absensis_by_guru as $guru_nama => $guru_absensis)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ $guru_nama }}</h5>
                </div>
                <div class="card-body">
                    @if ($guru_absensis->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Hari</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Jam Mengajar</th>
                                        <th>Waktu Absen</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($guru_absensis as $absensi)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ \Carbon\Carbon::parse($absensi->tanggal)->translatedFormat('d M Y') }}
                                            </td>
                                            <td>{{ ucfirst($absensi->jadwal->hari) }}</td>
                                            <td>{{ $absensi->jadwal->mataPelajaran->nama_mapel }}</td>
                                            <td>{{ $absensi->jadwal->jam_mulai }} - {{ $absensi->jadwal->jam_selesai }}
                                            </td>
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
                        <p class="text-muted text-center py-3">Belum ada data absensi</p>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($absensis_by_guru->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-2">Belum ada data absensi untuk periode ini</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
