@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
    <div class="container-fluid px-3 px-md-4">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <h1 class="fw-bold mb-1">Dashboard Guru</h1>
            <p class="text-muted mb-0">Selamat datang, <strong>{{ $guru->nama }}</strong>!
                <span class="badge bg-primary">{{ $hari_ini }}, {{ now()->format('d M Y') }}</span>
            </p>
        </div>

        <!-- Notifikasi & Peringatan - Tidak Bisa Di-dismiss -->
        {{-- PRIORITAS 1: Jadwal terlewat belum absen (KRITIS!) --}}
        @if ($jadwal_terlewat_pertama)
            <div class="alert alert-danger border-start border-danger border-4 mb-4 alert-permanent" role="alert">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-exclamation-octagon-fill fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-2">
                            <i class="bi bi-alarm-fill"></i> URGENT: Jadwal Terlewat Tanpa Absen!
                        </h5>
                        <p class="mb-2">
                            Anda <strong class="text-decoration-underline">TIDAK ABSEN</strong> untuk jadwal:
                            <strong>{{ $jadwal_terlewat_pertama->mataPelajaran->nama_mapel }}</strong>
                            di kelas <strong>{{ $jadwal_terlewat_pertama->kelas->nama_kelas }}</strong>
                        </p>
                        <p class="mb-3 small">
                            <i class="bi bi-clock-fill"></i>
                            {{ \Carbon\Carbon::parse($jadwal_terlewat_pertama->jam_mulai)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($jadwal_terlewat_pertama->jam_selesai)->format('H:i') }}
                            @if ($jadwal_terlewat_pertama->ruangan)
                                <span class="ms-2"><i class="bi bi-geo-alt-fill"></i>
                                    {{ $jadwal_terlewat_pertama->ruangan }}</span>
                            @endif
                            <span class="ms-2 badge bg-danger">Jadwal Sudah Lewat</span>
                        </p>
                        <p class="mb-3 small text-dark">
                            <i class="bi bi-info-circle-fill"></i>
                            Status: <strong>Akan tercatat ALPHA</strong> jika tidak segera melakukan absensi atau mengajukan
                            izin
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('absensi.scan-qr') }}" class="btn btn-danger btn-sm">
                                <i class="bi bi-qr-code-scan"></i> Absen Sekarang
                            </a>
                            <a href="{{ route('guru.izin.create') }}" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-file-earmark-text"></i> Ajukan Izin
                            </a>
                        </div>

                        @if ($jadwal_terlewat_belum_absen->count() > 1)
                            <div class="alert alert-warning mt-3 mb-0 py-2 px-3" role="alert">
                                <small>
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <strong>Perhatian:</strong> Anda memiliki
                                    <strong>{{ $jadwal_terlewat_belum_absen->count() }}</strong> jadwal yang terlewat tanpa
                                    absen hari ini
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- PRIORITAS 2: Jadwal sedang berlangsung belum absen --}}
        @elseif ($jadwal_berlangsung && !$sudah_absen_jadwal_berlangsung)
            <div class="alert alert-danger border-start border-danger border-4 mb-4 alert-permanent" role="alert">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-2">
                            <i class="bi bi-bell-fill"></i> Peringatan: Anda Belum Absen!
                        </h5>
                        <p class="mb-2">Jadwal mengajar sedang berlangsung:
                            <strong>{{ $jadwal_berlangsung->mataPelajaran->nama_mapel }}</strong>
                            di kelas <strong>{{ $jadwal_berlangsung->kelas->nama_kelas }}</strong>
                        </p>
                        <p class="mb-3 small">
                            <i class="bi bi-clock"></i>
                            {{ \Carbon\Carbon::parse($jadwal_berlangsung->jam_mulai)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($jadwal_berlangsung->jam_selesai)->format('H:i') }}
                            @if ($jadwal_berlangsung->ruangan)
                                <span class="ms-2"><i class="bi bi-geo-alt"></i>
                                    {{ $jadwal_berlangsung->ruangan }}</span>
                            @endif
                        </p>
                        <div class="d-flex gap-2">
                            <a href="{{ route('absensi.scan-qr') }}" class="btn btn-danger btn-sm">
                                <i class="bi bi-qr-code-scan"></i> Scan QR Code
                            </a>
                            <a href="{{ route('absensi.selfie') }}" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-camera"></i> Absensi Selfie
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($jadwal_upcoming)
            <div class="alert alert-warning border-start border-warning border-4 mb-4 alert-permanent" role="alert">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-2">
                            <i class="bi bi-bell"></i> Jadwal Mengajar Akan Dimulai Segera
                        </h6>
                        <p class="mb-2">
                            <strong>{{ $jadwal_upcoming->mataPelajaran->nama_mapel }}</strong>
                            di kelas <strong>{{ $jadwal_upcoming->kelas->nama_kelas }}</strong>
                        </p>
                        <p class="mb-0 small">
                            <i class="bi bi-clock"></i>
                            Pukul {{ \Carbon\Carbon::parse($jadwal_upcoming->jam_mulai)->format('H:i') }}
                            @if ($jadwal_upcoming->ruangan)
                                <span class="ms-2"><i class="bi bi-geo-alt"></i> {{ $jadwal_upcoming->ruangan }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @elseif($jadwal_hari_ini->count() > 0 && $absensi_hari_ini == 0)
            <div class="alert alert-info border-start border-info border-4 mb-4" role="alert">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-info-circle-fill fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-2">
                            <i class="bi bi-calendar-check"></i> Anda Memiliki {{ $jadwal_hari_ini->count() }} Jadwal Hari
                            Ini
                        </h6>
                        <p class="mb-0 small">Jangan lupa melakukan absensi sebelum atau saat jadwal mengajar dimulai</p>
                    </div>
                </div>
            </div>
        @elseif($jadwal_hari_ini->count() == 0)
            <div class="alert alert-secondary border-start border-secondary border-4 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-calendar-x fs-3 me-3"></i>
                    <div>
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i> Tidak ada jadwal mengajar hari ini
                        </h6>
                    </div>
                </div>
            </div>
        @endif

        <!-- Statistik Bulan Ini -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-success bg-opacity-10 text-success rounded p-2 me-2">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h2 class="fw-bold mb-0">{{ $total_hadir }}</h2>
                                <p class="text-muted mb-0 small">Hadir</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-warning bg-opacity-10 text-warning rounded p-2 me-2">
                                <i class="bi bi-clock-fill fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h2 class="fw-bold mb-0">{{ $total_terlambat }}</h2>
                                <p class="text-muted mb-0 small">Terlambat</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-info bg-opacity-10 text-info rounded p-2 me-2">
                                <i class="bi bi-file-text-fill fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h2 class="fw-bold mb-0">{{ $total_izin }}</h2>
                                <p class="text-muted mb-0 small">Izin/Sakit</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-danger bg-opacity-10 text-danger rounded p-2 me-2">
                                <i class="bi bi-x-circle-fill fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h2 class="fw-bold mb-0">{{ $total_alpha }}</h2>
                                <p class="text-muted mb-0 small">Alpha</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Jadwal Hari Ini -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-calendar-day text-primary"></i> Jadwal Mengajar Hari Ini
                            </h5>
                            @if ($jadwal_hari_ini->count() > 0)
                                <span class="badge bg-primary">{{ $jadwal_hari_ini->count() }} Jadwal</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-3">
                        @if ($jadwal_hari_ini->count() > 0)
                            @foreach ($jadwal_hari_ini as $jadwal)
                                @php
                                    $jam_mulai = \Carbon\Carbon::parse($jadwal->jam_mulai);
                                    $jam_selesai = \Carbon\Carbon::parse($jadwal->jam_selesai);
                                    $is_active = now()->between($jam_mulai, $jam_selesai);
                                    $is_upcoming =
                                        now()->diffInMinutes($jam_mulai, false) > 0 &&
                                        now()->diffInMinutes($jam_mulai, false) <= 30;

                                    // Cek status absensi untuk jadwal ini
                                    $absensi_jadwal = \App\Models\Absensi::where('guru_id', $guru->id)
                                        ->where('jadwal_id', $jadwal->id)
                                        ->whereDate('tanggal', today())
                                        ->first();

                                    $is_terlewat = now()->greaterThan($jam_selesai);
                                @endphp
                                <div
                                    class="card jadwal-card mb-2 {{ $is_active ? 'jadwal-active' : ($is_upcoming ? 'jadwal-upcoming' : '') }}">
                                    <div class="card-body p-3">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-12 col-sm-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-clock text-primary me-2"></i>
                                                    <strong>{{ $jam_mulai->format('H:i') }} -
                                                        {{ $jam_selesai->format('H:i') }}</strong>
                                                </div>
                                                @if ($is_active)
                                                    <span class="badge bg-success mt-1 small">Sedang Berlangsung</span>
                                                @elseif($is_upcoming)
                                                    <span class="badge bg-warning mt-1 small">Segera Dimulai</span>
                                                @elseif($is_terlewat && !$absensi_jadwal)
                                                    <span class="badge bg-danger mt-1 small">Terlewat - Belum Absen</span>
                                                @elseif($is_terlewat && $absensi_jadwal)
                                                    <span class="badge bg-secondary mt-1 small">Selesai</span>
                                                @endif
                                            </div>
                                            <div class="col-6 col-sm-2">
                                                <div class="small text-muted">Kelas</div>
                                                <span
                                                    class="badge bg-info bg-opacity-10 text-info">{{ $jadwal->kelas->nama_kelas }}</span>
                                            </div>
                                            <div class="col-6 col-sm-4">
                                                <div class="small text-muted">Mata Pelajaran</div>
                                                <div class="fw-semibold">{{ $jadwal->mataPelajaran->nama_mapel }}</div>
                                            </div>
                                            <div class="col-12 col-sm-3">
                                                <div class="d-flex flex-column">
                                                    <div>
                                                        <div class="small text-muted">Ruangan</div>
                                                        <div class="text-muted">
                                                            <i class="bi bi-geo-alt"></i>
                                                            {{ $jadwal->ruangan ?? 'Belum ditentukan' }}
                                                        </div>
                                                    </div>
                                                    @if ($absensi_jadwal)
                                                        <div class="mt-2">
                                                            <span
                                                                class="badge
                                                                @if ($absensi_jadwal->status_kehadiran == 'hadir') bg-success
                                                                @elseif($absensi_jadwal->status_kehadiran == 'terlambat') bg-warning
                                                                @elseif(in_array($absensi_jadwal->status_kehadiran, ['izin', 'sakit'])) bg-info
                                                                @else bg-danger @endif
                                                            ">
                                                                <i class="bi bi-check-circle"></i>
                                                                {{ ucfirst($absensi_jadwal->status_kehadiran) }}
                                                            </span>
                                                        </div>
                                                    @elseif($is_terlewat)
                                                        <div class="mt-2">
                                                            <span class="badge bg-danger">
                                                                <i class="bi bi-x-circle"></i> Belum Absen
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x" style="font-size: 4rem;"></i>
                                <p class="mt-3 mb-0">Tidak ada jadwal mengajar hari ini</p>
                                <small>Anda dapat beristirahat atau melakukan aktivitas lainnya</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-lightning-fill text-warning"></i> Aksi Cepat
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            <a href="{{ route('absensi.scan-qr') }}" class="btn btn-primary">
                                <i class="bi bi-qr-code-scan"></i> Scan QR Code
                            </a>
                            <a href="{{ route('absensi.selfie') }}" class="btn btn-success">
                                <i class="bi bi-camera"></i> Absensi Selfie
                            </a>
                            <a href="{{ route('guru.absensi.riwayat') }}" class="btn btn-outline-info">
                                <i class="bi bi-clock-history"></i> Riwayat Absensi
                            </a>
                            <a href="{{ route('guru.jadwal.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-calendar3"></i> Lihat Jadwal Lengkap
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info Tambahan -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-info-circle text-info"></i> Informasi
                        </h6>
                        <div class="small text-muted">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-calendar-check me-2"></i>
                                <span>Total absensi bulan ini: <strong>{{ $absensi_bulan_ini->count() }}</strong></span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-calendar-event me-2"></i>
                                <span>Absensi hari ini: <strong>{{ $absensi_hari_ini }}</strong></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-percent me-2"></i>
                                <span>Tingkat kehadiran:
                                    <strong>
                                        @if ($absensi_bulan_ini->count() > 0)
                                            {{ number_format(($total_hadir / $absensi_bulan_ini->count()) * 100, 1) }}%
                                        @else
                                            0%
                                        @endif
                                    </strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/guru-dashboard.js') }}"></script>
@endpush
