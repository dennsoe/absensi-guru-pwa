@extends('layouts.app')

@section('title', 'Laporan Per Mata Pelajaran')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('kurikulum.laporan.index') }}" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h4 class="mb-1">Laporan Per Mata Pelajaran</h4>
            <p class="text-muted mb-0">Analisis kehadiran berdasarkan mata pelajaran</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Mata Pelajaran</label>
                            <select name="mapel_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Mata Pelajaran</option>
                                @foreach($mapel_list as $m)
                                <option value="{{ $m->id }}" {{ request('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama_mapel }}</option>
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
            <h5 class="mb-0">Data Kehadiran Per Mata Pelajaran</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Mata Pelajaran</th>
                            <th>Total Guru</th>
                            <th>Total Jadwal</th>
                            <th>Hadir</th>
                            <th>Terlambat</th>
                            <th>Izin</th>
                            <th>Alpha</th>
                            <th>Total Pertemuan</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $index => $lap)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $lap['mapel']->nama_mapel }}</strong></td>
                            <td>{{ $lap['total_guru'] }}</td>
                            <td>{{ $lap['total_jadwal'] }}</td>
                            <td><span class="badge bg-success">{{ $lap['hadir'] }}</span></td>
                            <td><span class="badge bg-warning">{{ $lap['terlambat'] }}</span></td>
                            <td><span class="badge bg-info">{{ $lap['izin'] }}</span></td>
                            <td><span class="badge bg-danger">{{ $lap['alpha'] }}</span></td>
                            <td>{{ $lap['total_pertemuan'] }}</td>
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

    <!-- Top Performers by Subject -->
    @if(!empty($top_performers))
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Top Performers Per Mata Pelajaran</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($top_performers as $mapel_name => $guru)
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="text-primary">{{ $mapel_name }}</h6>
                            <p class="mb-1"><strong>{{ $guru['nama'] }}</strong></p>
                            <p class="mb-0 small text-muted">NIP: {{ $guru['nip'] }}</p>
                            <p class="mb-0 text-success"><strong>{{ $guru['persentase'] }}%</strong> kehadiran</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
