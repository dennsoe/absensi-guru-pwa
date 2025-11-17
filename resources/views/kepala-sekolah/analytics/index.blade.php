@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h4 class="mb-1">Analytics & Statistik Lanjutan</h4>
                <p class="text-muted mb-0">Analisis mendalam tentang kehadiran guru</p>
            </div>
        </div>

        <!-- 30 Days Trend -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Trend Kehadiran 30 Hari Terakhir</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6 Month Comparison -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Perbandingan 6 Bulan Terakhir</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Performers -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Top 10 Guru Terbaik</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Guru</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($top_performers as $index => $guru)
                                        <tr>
                                            <td>
                                                @if ($index === 0)
                                                    <span class="badge bg-warning">🥇</span>
                                                @elseif($index === 1)
                                                    <span class="badge bg-secondary">🥈</span>
                                                @elseif($index === 2)
                                                    <span class="badge bg-info">🥉</span>
                                                @else
                                                    {{ $index + 1 }}
                                                @endif
                                            </td>
                                            <td><strong>{{ $guru->nama }}</strong></td>
                                            <td class="text-center">{{ $guru->total_absensi }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-success">{{ number_format($guru->persentase, 1) }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Violations -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Top 10 Guru dengan Pelanggaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Guru</th>
                                        <th class="text-center">Terlambat</th>
                                        <th class="text-center">Alpha</th>
                                        <th class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($top_violations as $index => $guru)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $guru->nama }}</strong></td>
                                            <td class="text-center">
                                                <span class="badge bg-warning">{{ $guru->total_terlambat }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger">{{ $guru->total_alpha }}</span>
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ $guru->total_pelanggaran }}</strong>
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

        <!-- Stats by Day of Week -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Statistik Per Hari dalam Seminggu</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dayOfWeekChart" height="60"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // 30 Days Trend Chart
            const trendData = @json($trend_30_days);
            new Chart(document.getElementById('trendChart'), {
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

            // 6 Month Comparison Chart
            const monthlyData = @json($comparison_6_months);
            new Chart(document.getElementById('monthlyChart'), {
                type: 'bar',
                data: {
                    labels: monthlyData.map(d => d.bulan),
                    datasets: [{
                        label: 'Hadir',
                        data: monthlyData.map(d => d.hadir),
                        backgroundColor: '#28a745'
                    }, {
                        label: 'Terlambat',
                        data: monthlyData.map(d => d.terlambat),
                        backgroundColor: '#ffc107'
                    }, {
                        label: 'Alpha',
                        data: monthlyData.map(d => d.alpha),
                        backgroundColor: '#dc3545'
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
                            beginAtZero: true,
                            stacked: false
                        }
                    }
                }
            });

            // Day of Week Chart
            const dayData = @json($stats_per_day);
            new Chart(document.getElementById('dayOfWeekChart'), {
                type: 'bar',
                data: {
                    labels: dayData.map(d => d.hari),
                    datasets: [{
                        label: 'Kehadiran',
                        data: dayData.map(d => d.total_hadir),
                        backgroundColor: '#007bff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
