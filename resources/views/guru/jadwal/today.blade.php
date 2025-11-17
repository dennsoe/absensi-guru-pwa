@extends('layouts.app')

@section('title', 'Jadwal Hari Ini')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h4 class="mb-1">Jadwal Hari Ini</h4>
                <p class="text-muted mb-0">{{ ucfirst(\Carbon\Carbon::now()->dayName) }},
                    {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
            </div>
        </div>

        @if ($jadwal_today->isEmpty())
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle" style="font-size: 48px;"></i>
                <h5 class="mt-3">Tidak Ada Jadwal Hari Ini</h5>
                <p class="mb-0">Anda tidak memiliki jadwal mengajar pada hari ini.</p>
            </div>
        @else
            <!-- Schedule Timeline -->
            <div class="row">
                @foreach ($jadwal_today as $j)
                    <div class="col-md-6 mb-3">
                        <div
                            class="card h-100 {{ \Carbon\Carbon::now()->format('H:i') >= substr($j->jam_mulai, 0, 5) && \Carbon\Carbon::now()->format('H:i') <= substr($j->jam_selesai, 0, 5) ? 'border-primary' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1">{{ $j->mataPelajaran->nama_mapel }}</h5>
                                        <p class="mb-1 text-muted">{{ $j->kelas->nama_kelas }}</p>
                                    </div>
                                    @if (
                                        \Carbon\Carbon::now()->format('H:i') >= substr($j->jam_mulai, 0, 5) &&
                                            \Carbon\Carbon::now()->format('H:i') <= substr($j->jam_selesai, 0, 5))
                                        <span class="badge bg-primary">Sedang Berlangsung</span>
                                    @elseif(\Carbon\Carbon::now()->format('H:i') < substr($j->jam_mulai, 0, 5))
                                        <span class="badge bg-info">Akan Datang</span>
                                    @else
                                        <span class="badge bg-secondary">Selesai</span>
                                    @endif
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1 small text-muted">
                                            <i class="bi bi-clock"></i> Jam
                                        </p>
                                        <p class="mb-2"><strong>{{ substr($j->jam_mulai, 0, 5) }} -
                                                {{ substr($j->jam_selesai, 0, 5) }}</strong></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1 small text-muted">
                                            <i class="bi bi-geo-alt"></i> Ruangan
                                        </p>
                                        <p class="mb-2"><strong>{{ $j->ruangan ?? '-' }}</strong></p>
                                    </div>
                                </div>

                                @php
                                    $absensi = $absensi_today->where('jadwal_mengajar_id', $j->id)->first();
                                @endphp

                                @if ($absensi)
                                    <div class="alert alert-success mb-0 mt-2">
                                        <i class="bi bi-check-circle"></i>
                                        <strong>Sudah Absen</strong><br>
                                        <small>Masuk: {{ substr($absensi->jam_masuk, 0, 5) }}</small>
                                        @if ($absensi->jam_keluar)
                                            <small> | Keluar: {{ substr($absensi->jam_keluar, 0, 5) }}</small>
                                        @endif
                                    </div>
                                @elseif(\Carbon\Carbon::now()->format('H:i') >= substr($j->jam_mulai, 0, 5))
                                    <div class="alert alert-warning mb-0 mt-2">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Belum Absen</strong><br>
                                        <small>Silakan scan QR Code untuk absen masuk</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Quick Stats -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $jadwal_today->count() }}</h3>
                            <p class="text-muted mb-0 small">Total Jadwal Hari Ini</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $sudah_absen }}</h3>
                            <p class="text-muted mb-0 small">Sudah Absen</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $jadwal_today->count() - $sudah_absen }}</h3>
                            <p class="text-muted mb-0 small">Belum Absen</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
