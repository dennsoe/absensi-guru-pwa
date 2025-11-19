@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Dashboard Admin</h2>
            <p class="text-muted mb-0">Selamat datang di SIAG NEKAS - Sistem Informasi Absensi Guru</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i>
                <span class="d-none d-md-inline ms-2">Refresh</span>
            </button>
            <a href="{{ route('admin.laporan.index') }}" class="btn btn-primary">
                <i class="bi bi-file-bar-graph"></i>
                <span class="d-none d-md-inline ms-2">Lihat Laporan</span>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Guru -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-primary-light text-primary">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small mb-1">Total Guru</div>
                            <div class="fs-4 fw-bold">{{ $total_guru ?? 0 }}</div>
                            <div class="text-success small">
                                <i class="bi bi-arrow-up"></i> {{ $guru_aktif ?? 0 }} Aktif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-success-light text-success">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small mb-1">Hadir Hari Ini</div>
                            <div class="fs-4 fw-bold">{{ $guru_hadir_hari_ini ?? 0 }}</div>
                            <div class="text-muted small">
                                {{ $persentase_hadir ?? 0 }}% dari total
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Izin Pending -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-warning-light text-warning">
                                <i class="bi bi-clock-fill fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small mb-1">Izin Pending</div>
                            <div class="fs-4 fw-bold">{{ $izin_pending ?? 0 }}</div>
                            <a href="{{ route('admin.izin.index', ['status' => 'pending']) }}"
                                class="text-warning small text-decoration-none">
                                Lihat detail <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terlambat Hari Ini -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon bg-danger-light text-danger">
                                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small mb-1">Terlambat</div>
                            <div class="fs-4 fw-bold">{{ $guru_terlambat_hari_ini ?? 0 }}</div>
                            <div class="text-danger small">
                                Hari Ini
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Absensi Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Grafik Absensi 7 Hari Terakhir</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-download me-2"></i>Download</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-printer me-2"></i>Print</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="absensiChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Status Hari Ini</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>

                    <!-- Legend -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">&nbsp;</span>
                                <span class="small">Hadir</span>
                            </div>
                            <span class="fw-semibold">{{ $guru_hadir_hari_ini ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2">&nbsp;</span>
                                <span class="small">Izin/Sakit</span>
                            </div>
                            <span class="fw-semibold">{{ $guru_izin_hari_ini ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2">&nbsp;</span>
                                <span class="small">Terlambat</span>
                            </div>
                            <span class="fw-semibold">{{ $guru_terlambat_hari_ini ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2">&nbsp;</span>
                                <span class="small">Alpha</span>
                            </div>
                            <span class="fw-semibold">{{ $alpha_hari_ini ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Attendance & Pending Izin -->
    <div class="row g-4 mb-4">
        <!-- Absensi Terbaru -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Absensi Terbaru</h5>
                    <a href="{{ route('admin.absensi.index') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Nama Guru</th>
                                    <th>Status</th>
                                    <th>Metode</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latest_absensi ?? [] as $absensi)
                                    <tr>
                                        <td>
                                            <div class="small text-muted">{{ $absensi->jam_masuk ?? '-' }}</div>
                                            <div class="small">
                                                {{ \Carbon\Carbon::parse($absensi->tanggal)->format('d M Y') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $absensi->guru->nama ?? 'N/A' }}</div>
                                            <div class="small text-muted">{{ $absensi->guru->nip ?? '-' }}</div>
                                        </td>
                                        <td>
                                            @if ($absensi->status_kehadiran === 'Hadir')
                                                <span
                                                    class="badge bg-success-light text-success">{{ $absensi->status_kehadiran }}</span>
                                            @elseif($absensi->status_kehadiran === 'Terlambat')
                                                <span
                                                    class="badge bg-warning-light text-warning">{{ $absensi->status_kehadiran }}</span>
                                            @elseif($absensi->status_kehadiran === 'Alpha')
                                                <span
                                                    class="badge bg-danger-light text-danger">{{ $absensi->status_kehadiran }}</span>
                                            @else
                                                <span
                                                    class="badge bg-info-light text-info">{{ $absensi->status_kehadiran }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small text-muted">
                                                @if ($absensi->metode_absensi === 'qr')
                                                    <i class="bi bi-qr-code"></i> QR Code
                                                @else
                                                    <i class="bi bi-geo-alt"></i> GPS
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $absensi->status_kehadiran === 'hadir' ? 'success' : ($absensi->status_kehadiran === 'terlambat' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($absensi->status_kehadiran) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            Belum ada data absensi hari ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Izin Pending -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Permohonan Izin</h5>
                    <a href="{{ route('admin.izin.index') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($pending_izin ?? [] as $izin)
                            <a href="{{ route('admin.izin.show', $izin->id) }}"
                                class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small">{{ $izin->guru->nama ?? 'N/A' }}</div>
                                        <div class="text-muted small">{{ ucfirst($izin->jenis ?? 'Izin') }}</div>
                                        <div class="text-muted small">
                                            <i class="bi bi-calendar3"></i>
                                            {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M') }} -
                                            {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d M') }}
                                        </div>
                                    </div>
                                    <span class="badge bg-warning-light text-warning">Pending</span>
                                </div>
                            </a>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                                Tidak ada izin pending
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <a href="{{ route('admin.settings') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-primary-light text-primary">
                                    <i class="bi bi-gear"></i>
                                </div>
                                <div class="quick-action-title">Pengaturan Sistem</div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <a href="{{ route('admin.guru.create') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-success-light text-success">
                                    <i class="bi bi-person-plus"></i>
                                </div>
                                <div class="quick-action-title">Tambah Guru</div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <a href="{{ route('admin.jadwal.create') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-info-light text-info">
                                    <i class="bi bi-calendar-plus"></i>
                                </div>
                                <div class="quick-action-title">Buat Jadwal</div>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <a href="{{ route('admin.laporan.index') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-warning-light text-warning">
                                    <i class="bi bi-file-earmark-bar-graph"></i>
                                </div>
                                <div class="quick-action-title">Lihat Laporan</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .stat-icon {
                width: 56px;
                height: 56px;
                border-radius: var(--radius-lg);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .bg-primary-light {
                background-color: #E0E7FF !important;
            }

            .bg-success-light {
                background-color: #D1FAE5 !important;
            }

            .bg-warning-light {
                background-color: #FEF3C7 !important;
            }

            .bg-danger-light {
                background-color: #FEE2E2 !important;
            }

            .bg-info-light {
                background-color: #CFFAFE !important;
            }

            .quick-action-card {
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 1.5rem;
                background: var(--color-white);
                border: 1px solid var(--color-gray-200);
                border-radius: var(--radius-lg);
                text-decoration: none;
                color: var(--color-gray-900);
                transition: all 0.2s;
            }

            .quick-action-card:hover {
                border-color: var(--color-primary);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                transform: translateY(-2px);
            }

            .quick-action-icon {
                width: 56px;
                height: 56px;
                border-radius: var(--radius-lg);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                margin-bottom: 0.75rem;
            }

            .quick-action-title {
                font-weight: 500;
                font-size: 0.875rem;
                text-align: center;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Absensi Line Chart
                const absensiCtx = document.getElementById('absensiChart');
                if (absensiCtx) {
                    new Chart(absensiCtx, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($chart_labels ?? ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']) !!},
                            datasets: [{
                                    label: 'Hadir',
                                    data: {!! json_encode($chart_hadir ?? [45, 48, 47, 49, 46, 0, 0]) !!},
                                    borderColor: 'rgb(16, 185, 129)',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    tension: 0.4
                                },
                                {
                                    label: 'Izin',
                                    data: {!! json_encode($chart_izin ?? [2, 3, 1, 2, 3, 0, 0]) !!},
                                    borderColor: 'rgb(6, 182, 212)',
                                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
                                    tension: 0.4
                                },
                                {
                                    label: 'Alpha',
                                    data: {!! json_encode($chart_alpha ?? [3, 2, 3, 2, 4, 0, 0]) !!},
                                    borderColor: 'rgb(239, 68, 68)',
                                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                    tension: 0.4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }

                // Status Doughnut Chart
                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Hadir', 'Izin/Sakit', 'Terlambat', 'Alpha'],
                            datasets: [{
                                data: [
                                    {{ $guru_hadir_hari_ini ?? 0 }},
                                    {{ $guru_izin_hari_ini ?? 0 }},
                                    {{ $guru_terlambat_hari_ini ?? 0 }},
                                    {{ $alpha_hari_ini ?? 0 }}
                                ],
                                backgroundColor: [
                                    'rgb(16, 185, 129)',
                                    'rgb(6, 182, 212)',
                                    'rgb(245, 158, 11)',
                                    'rgb(239, 68, 68)'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                }
            });

            // Live Monitoring - Auto refresh every 30 seconds
            function refreshLiveStatus() {
                fetch('{{ route('admin.dashboard.live-guru-status') }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update summary statistics if needed
                            console.log('Live status updated:', data.summary);
                            // You can update DOM elements here with latest data
                        }
                    })
                    .catch(error => {
                        console.error('Failed to fetch live status:', error);
                    });
            }

            // Auto-refresh every 30 seconds
            setInterval(refreshLiveStatus, 30000);
        </script>
    @endpush
@endsection
