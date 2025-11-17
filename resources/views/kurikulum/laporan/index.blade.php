@extends('layouts.app')

@section('title', 'Laporan Akademik')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h4 class="mb-1">Laporan Akademik</h4>
                <p class="text-muted mb-0">Laporan kehadiran dan kinerja akademik</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $total_guru }}</h3>
                        <p class="text-muted mb-0 small">Total Guru</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $total_jadwal }}</h3>
                        <p class="text-muted mb-0 small">Total Jadwal</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $persentase_kehadiran }}%</h3>
                        <p class="text-muted mb-0 small">Kehadiran (30 hari)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $total_terlambat }}</h3>
                        <p class="text-muted mb-0 small">Total Terlambat</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Options -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-check" style="font-size: 48px; color: #0d6efd;"></i>
                        </div>
                        <h5>Laporan Per Guru</h5>
                        <p class="text-muted small">Lihat detail kehadiran dan kinerja setiap guru</p>
                        <a href="{{ route('kurikulum.laporan.per-guru') }}" class="btn btn-primary">
                            <i class="bi bi-file-earmark-text"></i> Lihat Laporan
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-book" style="font-size: 48px; color: #198754;"></i>
                        </div>
                        <h5>Laporan Per Mata Pelajaran</h5>
                        <p class="text-muted small">Analisis kehadiran berdasarkan mata pelajaran</p>
                        <a href="{{ route('kurikulum.laporan.per-mapel') }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-text"></i> Lihat Laporan
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-file-pdf" style="font-size: 48px; color: #dc3545;"></i>
                        </div>
                        <h5>Export PDF</h5>
                        <p class="text-muted small">Unduh laporan lengkap dalam format PDF</p>
                        <form action="{{ route('kurikulum.laporan.pdf') }}" method="GET">
                            <div class="mb-2">
                                <select name="bulan" class="form-select form-select-sm">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::createFromDate(null, $i, 1)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-download"></i> Download PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Aktivitas Terbaru (7 Hari)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Guru</th>
                                <th>Status</th>
                                <th>Jam Masuk</th>
                                <th>Jadwal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_activity as $a)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <strong>{{ $a->guru->nama }}</strong><br>
                                        <small class="text-muted">{{ $a->guru->nip }}</small>
                                    </td>
                                    <td>
                                        @if ($a->status === 'hadir')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($a->status === 'terlambat')
                                            <span class="badge bg-warning">Terlambat</span>
                                        @elseif($a->status === 'izin')
                                            <span class="badge bg-info">Izin</span>
                                        @else
                                            <span class="badge bg-danger">Alpha</span>
                                        @endif
                                    </td>
                                    <td>{{ $a->jam_masuk ? substr($a->jam_masuk, 0, 5) : '-' }}</td>
                                    <td>
                                        @if ($a->jadwalMengajar)
                                            <small>{{ $a->jadwalMengajar->kelas->nama_kelas }} -
                                                {{ $a->jadwalMengajar->mataPelajaran->nama_mapel }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada aktivitas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
