@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Dashboard Guru</h1>
        <p class="page-subtitle">Selamat datang, <strong>{{ $guru->nama }}</strong>! Berikut jadwal dan statistik absensi
            Anda.</p>
    </div>

    <div class="row">
        <!-- Statistik Bulan Ini -->
        <div class="col-md-3 mb-4">
            <div class="card stat-card border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Hadir Bulan Ini</h6>
                    <h2 class="mb-0 text-success">{{ $total_hadir }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card stat-card border-start border-4 border-warning">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Terlambat</h6>
                    <h2 class="mb-0 text-warning">{{ $total_terlambat }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card stat-card border-start border-4 border-info">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Izin/Sakit</h6>
                    <h2 class="mb-0 text-info">{{ $total_izin }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card stat-card border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Absensi</h6>
                    <h2 class="mb-0 text-primary">{{ $absensi_bulan_ini->count() }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Jadwal Hari Ini -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-day text-primary"></i> Jadwal Mengajar Hari Ini</h5>
                </div>
                <div class="card-body">
                    @if ($jadwal_hari_ini->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Ruangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($jadwal_hari_ini as $jadwal)
                                        <tr>
                                            <td><strong>{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</strong></td>
                                            <td><span class="badge bg-primary">{{ $jadwal->kelas->nama }}</span></td>
                                            <td>{{ $jadwal->mataPelajaran->nama }}</td>
                                            <td>{{ $jadwal->ruangan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x" style="font-size: 4rem;"></i>
                            <p class="mt-3">Tidak ada jadwal mengajar hari ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning-fill text-warning"></i> Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('absensi.scan-qr') }}" class="btn btn-primary">
                            <i class="bi bi-qr-code-scan"></i> Scan QR Code
                        </a>
                        <a href="{{ route('absensi.selfie') }}" class="btn btn-success">
                            <i class="bi bi-camera"></i> Absensi Selfie
                        </a>
                        <a href="{{ route('guru.absensi.riwayat') }}" class="btn btn-info">
                            <i class="bi bi-clock-history"></i> Riwayat Absensi
                        </a>
                        <a href="{{ route('jadwal.per-guru') }}" class="btn btn-secondary">
                            <i class="bi bi-calendar3"></i> Lihat Jadwal Lengkap
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .stat-card {
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>
@endpush
