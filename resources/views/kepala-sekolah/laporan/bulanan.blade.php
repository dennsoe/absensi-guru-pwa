@extends('layouts.app')

@section('title', 'Laporan Bulanan Eksekutif')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Laporan Absensi Bulanan</h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar"></i> {{ $bulan }} {{ $tahun }}
                    </p>
                </div>
                <a href="{{ route('kepala-sekolah.laporan.export-pdf') }}?bulan={{ request('bulan') }}&tahun={{ request('tahun') }}" class="btn btn-danger" target="_blank">
                    <i class="bi bi-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select" onchange="this.form.submit()">
                                @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('bulan', now()->month) == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $i)->locale('id')->translatedFormat('F') }}
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select" onchange="this.form.submit()">
                                @for($i = date('Y'); $i >= date('Y') - 2; $i--)
                                <option value="{{ $i }}" {{ request('tahun', now()->year) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $summary['total_guru'] }}</h3>
                    <p class="text-muted mb-0 small">Total Guru</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $summary['total_absensi'] }}</h3>
                    <p class="text-muted mb-0 small">Total Absensi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ number_format($summary['persentase_kehadiran'], 1) }}%</h3>
                    <p class="text-muted mb-0 small">Persentase Kehadiran</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $summary['total_pelanggaran'] }}</h3>
                    <p class="text-muted mb-0 small">Total Pelanggaran</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Table -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Detail Absensi Per Guru</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Guru</th>
                            <th>NIP</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Terlambat</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Alpha</th>
                            <th class="text-center">%</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $index => $l)
                        @php
                            $total = $l->hadir_count + $l->terlambat_count + $l->izin_count + $l->alpha_count;
                            $persentase = $total > 0 ? (($l->hadir_count + $l->terlambat_count) / $total * 100) : 0;
                        @endphp
                        <tr>
                            <td>{{ $laporan->firstItem() + $index }}</td>
                            <td><strong>{{ $l->nama }}</strong></td>
                            <td>{{ $l->nip }}</td>
                            <td class="text-center">{{ $total }}</td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $l->hadir_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">{{ $l->terlambat_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $l->izin_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $l->alpha_count }}</span>
                            </td>
                            <td class="text-center">
                                <strong class="text-{{ $persentase >= 80 ? 'success' : ($persentase >= 60 ? 'warning' : 'danger') }}">
                                    {{ number_format($persentase, 1) }}%
                                </strong>
                            </td>
                            <td>
                                <a href="{{ route('kepala-sekolah.analytics.per-guru', $l->id) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-graph-up"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">Tidak ada data laporan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($laporan->hasPages())
            <div class="mt-3">
                {{ $laporan->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
