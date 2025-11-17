@extends('layouts.app')

@section('title', 'Laporan Piket')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Laporan Piket</h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar-event"></i> {{ $tanggal }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('guru-piket.laporan.mingguan') }}" class="btn btn-info">
                        <i class="bi bi-calendar-week"></i> Laporan Mingguan
                    </a>
                    <a href="{{ route('guru-piket.laporan.export-pdf') }}?tanggal={{ $tanggal }}" class="btn btn-danger" target="_blank">
                        <i class="bi bi-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="row mb-4">
        <div class="col-md-4">
            <form method="GET" class="card">
                <div class="card-body">
                    <label class="form-label">Pilih Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $laporan->count() }}</h3>
                    <p class="text-muted mb-0 small">Total Guru</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success">{{ $laporan->sum('hadir') }}</h3>
                    <p class="text-muted mb-0 small">Total Hadir</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-warning">{{ $laporan->sum('terlambat') }}</h3>
                    <p class="text-muted mb-0 small">Total Terlambat</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-danger">{{ $laporan->sum('belum_absen') }}</h3>
                    <p class="text-muted mb-0 small">Belum Absen</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Table -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Detail Laporan Per Guru</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Guru</th>
                            <th>NIP</th>
                            <th class="text-center">Total Jadwal</th>
                            <th class="text-center">Sudah Absen</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Terlambat</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Belum Absen</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $index => $l)
                        <tr class="{{ $l->belum_absen > 0 ? 'table-warning' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $l->nama }}</strong></td>
                            <td>{{ $l->nip }}</td>
                            <td class="text-center">{{ $l->total_jadwal }}</td>
                            <td class="text-center">{{ $l->sudah_absen }}</td>
                            <td class="text-center">
                                @if($l->hadir > 0)
                                    <span class="badge bg-success">{{ $l->hadir }}</span>
                                @else
                                    {{ $l->hadir }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($l->terlambat > 0)
                                    <span class="badge bg-warning">{{ $l->terlambat }}</span>
                                @else
                                    {{ $l->terlambat }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($l->izin > 0)
                                    <span class="badge bg-info">{{ $l->izin }}</span>
                                @else
                                    {{ $l->izin }}
                                @endif
                            </td>
                            <td class="text-center">
                                @if($l->belum_absen > 0)
                                    <span class="badge bg-danger">{{ $l->belum_absen }}</span>
                                @else
                                    {{ $l->belum_absen }}
                                @endif
                            </td>
                            <td>
                                @if($l->terlambat > 0)
                                    <small class="text-warning">⚠️ Ada keterlambatan</small>
                                @elseif($l->belum_absen > 0)
                                    <small class="text-danger">❌ Belum lengkap</small>
                                @else
                                    <small class="text-success">✓ Lengkap</small>
                                @endif
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
        </div>
    </div>

    <!-- Notes Section -->
    @if($laporan->where('belum_absen', '>', 0)->count() > 0)
    <div class="alert alert-warning mt-4">
        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Perhatian</h5>
        <p class="mb-0">Terdapat {{ $laporan->where('belum_absen', '>', 0)->count() }} guru yang belum melengkapi absensi hari ini. Segera lakukan konfirmasi.</p>
    </div>
    @endif
</div>
@endsection
