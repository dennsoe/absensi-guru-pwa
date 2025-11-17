@extends('layouts.app')

@section('title', 'Monitoring Eksekutif')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Monitoring Absensi Eksekutif</h4>
                        <p class="text-muted mb-0">Dashboard monitoring untuk Kepala Sekolah</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select" id="periodFilter" onchange="window.location.href='?period='+this.value">
                            <option value="hari-ini" {{ $period === 'hari-ini' ? 'selected' : '' }}>Hari Ini</option>
                            <option value="minggu-ini" {{ $period === 'minggu-ini' ? 'selected' : '' }}>Minggu Ini</option>
                            <option value="bulan-ini" {{ $period === 'bulan-ini' ? 'selected' : '' }}>Bulan Ini</option>
                        </select>
                        <button type="button" class="btn btn-primary" onclick="refreshRealtime()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Statistics -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card stats-card border-primary">
                    <div class="card-body text-center">
                        <div class="icon-box bg-primary mb-2 mx-auto">
                            <i class="bi bi-people text-white"></i>
                        </div>
                        <h3 class="mb-0">{{ $statistics['total'] }}</h3>
                        <p class="text-muted mb-0 small">Total Absensi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card border-success">
                    <div class="card-body text-center">
                        <div class="icon-box bg-success mb-2 mx-auto">
                            <i class="bi bi-check-circle text-white"></i>
                        </div>
                        <h3 class="mb-0 text-success">{{ $statistics['hadir'] }}</h3>
                        <p class="text-muted mb-0 small">Hadir</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card border-warning">
                    <div class="card-body text-center">
                        <div class="icon-box bg-warning mb-2 mx-auto">
                            <i class="bi bi-clock-history text-white"></i>
                        </div>
                        <h3 class="mb-0 text-warning">{{ $statistics['terlambat'] }}</h3>
                        <p class="text-muted mb-0 small">Terlambat</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card border-info">
                    <div class="card-body text-center">
                        <div class="icon-box bg-info mb-2 mx-auto">
                            <i class="bi bi-file-text text-white"></i>
                        </div>
                        <h3 class="mb-0 text-info">{{ $statistics['izin'] }}</h3>
                        <p class="text-muted mb-0 small">Izin/Cuti</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card border-danger">
                    <div class="card-body text-center">
                        <div class="icon-box bg-danger mb-2 mx-auto">
                            <i class="bi bi-x-circle text-white"></i>
                        </div>
                        <h3 class="mb-0 text-danger">{{ $statistics['alpha'] }}</h3>
                        <p class="text-muted mb-0 small">Alpha</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card border-dark">
                    <div class="card-body text-center">
                        <div class="icon-box bg-dark mb-2 mx-auto">
                            <i class="bi bi-graph-up text-white"></i>
                        </div>
                        <h3 class="mb-0">{{ number_format($statistics['persentase_kehadiran'], 1) }}%</h3>
                        <p class="text-muted mb-0 small">Persentase</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Chart Section -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Trend Kehadiran 7 Hari Terakhir</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Top Violations -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Top 10 Guru dengan Pelanggaran Tertinggi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Ranking</th>
                                        <th>Guru</th>
                                        <th class="text-center">Terlambat</th>
                                        <th class="text-center">Alpha</th>
                                        <th class="text-center">Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($top_violations as $index => $guru)
                                        <tr>
                                            <td>
                                                @if ($index === 0)
                                                    <span class="badge bg-danger">1</span>
                                                @elseif($index === 1)
                                                    <span class="badge bg-warning">2</span>
                                                @elseif($index === 2)
                                                    <span class="badge bg-info">3</span>
                                                @else
                                                    {{ $index + 1 }}
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $guru->nama }}</strong><br>
                                                <small class="text-muted">{{ $guru->nip }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning">{{ $guru->total_terlambat }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger">{{ $guru->total_alpha }}</span>
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ $guru->total_pelanggaran }}</strong>
                                            </td>
                                            <td>
                                                <a href="{{ route('kepala-sekolah.monitoring.per-kelas', $guru->id) }}"
                                                    class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Tidak ada data pelanggaran
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Pending Approvals -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Izin Pending Approval</h6>
                        <a href="{{ route('kepala-sekolah.approval.index') }}" class="btn btn-sm btn-primary">Lihat
                            Semua</a>
                    </div>
                    <div class="card-body p-0">
                        @forelse($pending_izin as $izin)
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="d-block">{{ $izin->guru->nama }}</strong>
                                        <small class="text-muted">{{ $izin->jenis }}</small>
                                        <small class="d-block text-muted">
                                            {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d/m/Y') }} -
                                            {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d/m/Y') }}
                                        </small>
                                    </div>
                                    <a href="{{ route('kepala-sekolah.approval.show', $izin->id) }}"
                                        class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-3 text-center text-muted">
                                <i class="bi bi-check-circle"></i> Tidak ada izin pending
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Status Guru Hari Ini</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Guru Aktif</span>
                            <strong>{{ $total_guru_aktif ?? 0 }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Sudah Absen</span>
                            <strong class="text-success">{{ $statistics['hadir'] + $statistics['terlambat'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Belum Absen</span>
                            <strong
                                class="text-warning">{{ ($total_guru_aktif ?? 0) - ($statistics['hadir'] + $statistics['terlambat']) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .stats-card {
                border-top-width: 3px;
            }

            .icon-box {
                width: 40px;
                height: 40px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Trend Chart
            const trendData = @json($trend_data);
            const ctx = document.getElementById('trendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.map(d => d.tanggal),
                    datasets: [{
                        label: 'Hadir',
                        data: trendData.map(d => d.hadir),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Terlambat',
                        data: trendData.map(d => d.terlambat),
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Alpha',
                        data: trendData.map(d => d.alpha),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            function refreshRealtime() {
                window.location.reload();
            }
        </script>
    @endpush
@endsection
