@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Dashboard Admin</h1>
        <p class="page-subtitle">Selamat datang, {{ auth()->user()->guru->nama ?? auth()->user()->username }}! Berikut
            ringkasan sistem hari ini.</p>
    </div>

    <div class="row">
        <!-- Card Statistik -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Guru</h6>
                            <h2 class="mb-0">{{ $total_guru }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Kelas</h6>
                            <h2 class="mb-0">{{ $total_kelas }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Jadwal Aktif</h6>
                            <h2 class="mb-0">{{ $total_jadwal }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Hadir Hari Ini</h6>
                            <h2 class="mb-0">{{ $guru_hadir_hari_ini }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Guru Terlambat -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history text-warning"></i> Terlambat Hari Ini</h5>
                    <span class="badge bg-warning">{{ $guru_terlambat_hari_ini }}</span>
                </div>
                <div class="card-body">
                    @if ($guru_terlambat_hari_ini > 0)
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>{{ $guru_terlambat_hari_ini }} guru</strong> terlambat hari ini. Perlu tindak lanjut.
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0">Tidak ada guru yang terlambat hari ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Guru Izin -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text text-info"></i> Izin Hari Ini</h5>
                    <span class="badge bg-info">{{ $guru_izin_hari_ini }}</span>
                </div>
                <div class="card-body">
                    @if ($guru_izin_hari_ini > 0)
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle"></i>
                            <strong>{{ $guru_izin_hari_ini }} guru</strong> sedang izin/sakit/dinas luar hari ini.
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0">Tidak ada guru yang izin hari ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning-fill text-primary"></i> Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-person-plus-fill d-block mb-2" style="font-size: 2rem;"></i>
                                Tambah User Baru
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-people-fill d-block mb-2" style="font-size: 2rem;"></i>
                                Kelola User
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('laporan.index') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-graph-up d-block mb-2" style="font-size: 2rem;"></i>
                                Lihat Laporan
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="{{ route('jadwal.hari-ini') }}" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-calendar-day d-block mb-2" style="font-size: 2rem;"></i>
                                Jadwal Hari Ini
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .stat-card {
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
        }

        .stat-icon {
            font-size: 3rem;
            opacity: 0.3;
        }

        .stat-card h2 {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .stat-card h6 {
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
@endpush
