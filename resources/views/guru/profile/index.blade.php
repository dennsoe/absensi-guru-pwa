@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h4 class="mb-1">Profil Saya</h4>
            <p class="text-muted mb-0">Informasi data diri dan statistik kehadiran</p>
        </div>
    </div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if(auth()->user()->foto)
                        <img src="{{ Storage::url(auth()->user()->foto) }}" alt="Foto" class="rounded-circle" width="120" height="120" style="object-fit: cover;">
                        @else
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px; font-size: 48px;">
                            {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                        </div>
                        @endif
                    </div>
                    <h5 class="mb-1">{{ auth()->user()->nama }}</h5>
                    <p class="text-muted mb-2">{{ auth()->user()->nip }}</p>
                    <span class="badge bg-primary">{{ ucfirst(auth()->user()->role) }}</span>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('guru.profile.edit') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </a>
                        <a href="{{ route('guru.profile.change-password') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-key"></i> Ganti Password
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="card mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-telephone"></i> Kontak</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2"><i class="bi bi-envelope"></i> {{ auth()->user()->email }}</p>
                    @if(auth()->user()->no_hp)
                    <p class="small mb-0"><i class="bi bi-phone"></i> {{ auth()->user()->no_hp }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics & Info -->
        <div class="col-md-8">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h3 class="mb-0 text-success">{{ $stats['total_hadir'] }}</h3>
                            <p class="text-muted mb-0 small">Total Hadir</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h3 class="mb-0 text-warning">{{ $stats['total_izin'] }}</h3>
                            <p class="text-muted mb-0 small">Total Izin</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <h3 class="mb-0 text-danger">{{ $stats['total_alpha'] }}</h3>
                            <p class="text-muted mb-0 small">Total Alpha</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Info -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Lengkap</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">NIP</th>
                            <td>{{ auth()->user()->nip }}</td>
                        </tr>
                        <tr>
                            <th>Nama Lengkap</th>
                            <td>{{ auth()->user()->nama }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ auth()->user()->email }}</td>
                        </tr>
                        @if(auth()->user()->no_hp)
                        <tr>
                            <th>No. HP</th>
                            <td>{{ auth()->user()->no_hp }}</td>
                        </tr>
                        @endif
                        @if(auth()->user()->alamat)
                        <tr>
                            <th>Alamat</th>
                            <td>{{ auth()->user()->alamat }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Tanggal Bergabung</th>
                            <td>{{ \Carbon\Carbon::parse(auth()->user()->created_at)->format('d F Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Recent Attendance -->
            <div class="card mt-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Riwayat Absensi Terakhir (7 Hari)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_attendance as $a)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                                    <td>{{ $a->jam_masuk ? substr($a->jam_masuk, 0, 5) : '-' }}</td>
                                    <td>{{ $a->jam_keluar ? substr($a->jam_keluar, 0, 5) : '-' }}</td>
                                    <td>
                                        @if($a->status === 'hadir')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($a->status === 'terlambat')
                                            <span class="badge bg-warning">Terlambat</span>
                                        @elseif($a->status === 'izin')
                                            <span class="badge bg-info">Izin</span>
                                        @else
                                            <span class="badge bg-danger">Alpha</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data absensi</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
