@extends('layouts.app')

@section('title', 'Riwayat Absensi Hari Ini')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Riwayat Absensi Hari Ini</h2>
                <p class="text-muted mb-0">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
            </div>
            <a href="{{ route('guru.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Info Guru -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1">{{ $guru->nama }}</h5>
                        <p class="text-muted mb-0">NIP: {{ $guru->nip }}</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="badge bg-primary fs-6">Total: {{ $riwayat->count() }} Absensi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat -->
        @if ($riwayat->count() > 0)
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Waktu</th>
                                    <th>Jadwal</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Status</th>
                                    <th>Metode</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($riwayat as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold">
                                                {{ \Carbon\Carbon::parse($item->jam_absen)->format('H:i') }}</div>
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $item->jadwal->jam_mulai }} - {{ $item->jadwal->jam_selesai }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $item->jadwal->kelas->nama_kelas }}</span>
                                        </td>
                                        <td>{{ $item->jadwal->mataPelajaran->nama_mapel }}</td>
                                        <td>
                                            @if ($item->status_kehadiran == 'hadir')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Hadir
                                                </span>
                                            @elseif($item->status_kehadiran == 'terlambat')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock me-1"></i>Terlambat
                                                </span>
                                            @elseif($item->status_kehadiran == 'izin')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-envelope me-1"></i>Izin
                                                </span>
                                            @elseif($item->status_kehadiran == 'sakit')
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-heart-broken me-1"></i>Sakit
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle me-1"></i>Alpha
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->metode_absensi == 'qr_code')
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-qrcode me-1"></i>QR Code
                                                </span>
                                            @elseif($item->metode_absensi == 'selfie')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-camera me-1"></i>Selfie
                                                </span>
                                            @elseif($item->metode_absensi == 'manual')
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-hand-paper me-1"></i>Manual
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->keterangan)
                                                <small>{{ $item->keterangan }}</small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h5>Belum Ada Absensi Hari Ini</h5>
                    <p class="text-muted">Anda belum melakukan absensi untuk hari ini</p>
                    <div class="mt-4">
                        <a href="{{ route('guru.absensi.scan-qr') }}" class="btn btn-primary me-2">
                            <i class="fas fa-qrcode me-2"></i>Scan QR Code
                        </a>
                        <a href="{{ route('guru.absensi.selfie') }}" class="btn btn-success">
                            <i class="fas fa-camera me-2"></i>Absensi Selfie
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Summary Card -->
        @if ($riwayat->count() > 0)
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-0">{{ $riwayat->where('status_kehadiran', 'hadir')->count() }}</h4>
                            <small class="text-muted">Hadir</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h4 class="mb-0">{{ $riwayat->where('status_kehadiran', 'terlambat')->count() }}</h4>
                            <small class="text-muted">Terlambat</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="fas fa-envelope fa-2x text-info mb-2"></i>
                            <h4 class="mb-0">{{ $riwayat->whereIn('status_kehadiran', ['izin', 'sakit'])->count() }}</h4>
                            <small class="text-muted">Izin/Sakit</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                            <h4 class="mb-0">{{ $riwayat->where('status_kehadiran', 'alpha')->count() }}</h4>
                            <small class="text-muted">Alpha</small>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .table td {
            vertical-align: middle;
        }

        .badge {
            padding: 0.5rem 0.75rem;
        }
    </style>
@endpush
