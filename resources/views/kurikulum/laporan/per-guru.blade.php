@extends('layouts.app')

@section('title', 'Laporan Per Guru')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('kurikulum.laporan.index') }}" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h4 class="mb-1">Laporan Per Guru</h4>
            <p class="text-muted mb-0">Detail kehadiran dan kinerja setiap guru</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Guru</label>
                            <select name="guru_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Guru</option>
                                @foreach($guru_list as $g)
                                <option value="{{ $g->id }}" {{ request('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select" onchange="this.form.submit()">
                                @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('bulan', date('n')) == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select" onchange="this.form.submit()">
                                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                <option value="{{ $y }}" {{ request('tahun', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Data Kehadiran Guru</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Guru</th>
                            <th>NIP</th>
                            <th>Hadir</th>
                            <th>Terlambat</th>
                            <th>Izin</th>
                            <th>Alpha</th>
                            <th>Total Hari</th>
                            <th>Persentase</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $index => $lap)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $lap['guru']->nama }}</strong></td>
                            <td>{{ $lap['guru']->nip }}</td>
                            <td><span class="badge bg-success">{{ $lap['hadir'] }}</span></td>
                            <td><span class="badge bg-warning">{{ $lap['terlambat'] }}</span></td>
                            <td><span class="badge bg-info">{{ $lap['izin'] }}</span></td>
                            <td><span class="badge bg-danger">{{ $lap['alpha'] }}</span></td>
                            <td>{{ $lap['total_hari'] }}</td>
                            <td>
                                <strong 
                                    class="
                                    @if($lap['persentase'] >= 90) text-success
                                    @elseif($lap['persentase'] >= 75) text-warning
                                    @else text-danger
                                    @endif
                                    ">
                                    {{ number_format($lap['persentase'], 1) }}%
                                </strong>
                            </td>
                            <td>
                                @if($lap['persentase'] >= 90)
                                    <span class="badge bg-success">Sangat Baik</span>
                                @elseif($lap['persentase'] >= 75)
                                    <span class="badge bg-warning">Baik</span>
                                @else
                                    <span class="badge bg-danger">Perlu Perhatian</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
