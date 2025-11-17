@extends('layouts.app')

@section('title', 'Jadwal Hari Ini')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Jadwal Mengajar Hari Ini</h2>
                <p class="text-muted mb-0">{{ $hari }}, {{ now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Summary Card -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-day fa-2x text-primary mb-2"></i>
                        <h3 class="mb-0">{{ $jadwal->count() }}</h3>
                        <small class="text-muted">Total Jadwal Hari Ini</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-success mb-2"></i>
                        <h3 class="mb-0">{{ $jadwal->unique('guru_id')->count() }}</h3>
                        <small class="text-muted">Guru Mengajar</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-door-open fa-2x text-info mb-2"></i>
                        <h3 class="mb-0">{{ $jadwal->unique('kelas_id')->count() }}</h3>
                        <small class="text-muted">Kelas Aktif</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadwal Table -->
        @if ($jadwal->count() > 0)
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Jadwal</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Waktu</th>
                                    <th>Guru</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jadwal as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</div>
                                            <small class="text-muted">{{ $item->durasi }} menit</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($item->guru->foto)
                                                    <img src="{{ asset('storage/' . $item->guru->foto) }}"
                                                        class="rounded-circle me-2" width="40" height="40"
                                                        alt="{{ $item->guru->nama }}">
                                                @else
                                                    <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                        style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ $item->guru->nama }}</div>
                                                    <small class="text-muted">{{ $item->guru->nip }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info fs-6">{{ $item->mataPelajaran->nama_mapel }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary fs-6">{{ $item->kelas->nama_kelas }}</span>
                                        </td>
                                        <td>
                                            @if ($item->status == 'aktif')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Aktif
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times-circle me-1"></i>Non-Aktif
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h5>Tidak Ada Jadwal Hari Ini</h5>
                    <p class="text-muted">Tidak ada jadwal mengajar untuk hari {{ $hari }}</p>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .table td {
            vertical-align: middle;
        }

        .badge {
            padding: 0.5rem 0.75rem;
        }
    </style>
@endpush
