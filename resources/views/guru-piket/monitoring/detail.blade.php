@extends('layouts.app')

@section('title', 'Detail Absensi Guru')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('guru-piket.monitoring.index') }}" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h4 class="mb-1">Detail Absensi Guru</h4>
            <p class="text-muted mb-0">
                <i class="bi bi-calendar-event"></i> {{ $tanggal }}
            </p>
        </div>
    </div>

    <!-- Guru Profile Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>{{ $guru->nama }}</h5>
                            <p class="text-muted mb-2">NIP: {{ $guru->nip }}</p>
                            <p class="mb-1"><i class="bi bi-envelope"></i> {{ $guru->user->email }}</p>
                            <p class="mb-0"><i class="bi bi-telephone"></i> {{ $guru->no_telepon ?? '-' }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-{{ $guru->status === 'aktif' ? 'success' : 'danger' }} fs-6">
                                {{ strtoupper($guru->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $statistics['total'] }}</h3>
                    <p class="text-muted mb-0 small">Total Jadwal</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success">{{ $statistics['hadir'] }}</h3>
                    <p class="text-muted mb-0 small">Hadir</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-warning">{{ $statistics['terlambat'] }}</h3>
                    <p class="text-muted mb-0 small">Terlambat</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-danger">{{ $statistics['belum_absen'] }}</h3>
                    <p class="text-muted mb-0 small">Belum Absen</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Detail -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Jadwal Mengajar Hari Ini</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Jam</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Ruangan</th>
                            <th>Status Absen</th>
                            <th>Jam Absen</th>
                            <th>Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal as $j)
                        <tr>
                            <td>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</td>
                            <td>{{ $j->kelas->nama_kelas }}</td>
                            <td>{{ $j->mataPelajaran->nama_mapel }}</td>
                            <td>{{ $j->ruangan ?? '-' }}</td>
                            <td>
                                @php
                                    $absensi = $j->absensi->first();
                                @endphp
                                @if($absensi)
                                    @if($absensi->status === 'hadir')
                                        <span class="badge bg-success">Hadir</span>
                                    @elseif($absensi->status === 'terlambat')
                                        <span class="badge bg-warning">Terlambat</span>
                                    @elseif($absensi->status === 'izin')
                                        <span class="badge bg-info">Izin</span>
                                    @else
                                        <span class="badge bg-danger">Alpha</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Belum Absen</span>
                                @endif
                            </td>
                            <td>
                                @if($absensi)
                                    {{ substr($absensi->jam_absen, 0, 5) }}
                                    @if($absensi->status === 'terlambat')
                                        <br><small class="text-danger">Terlambat {{ $absensi->menit_keterlambatan }} menit</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($absensi)
                                    @if($absensi->metode === 'qr')
                                        <span class="badge bg-primary">QR Code</span>
                                    @elseif($absensi->metode === 'selfie')
                                        <span class="badge bg-info">Selfie</span>
                                    @else
                                        <span class="badge bg-secondary">Manual</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada jadwal untuk hari ini</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
