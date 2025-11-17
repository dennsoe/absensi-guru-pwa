@extends('layouts.app')

@section('title', 'Jadwal Per Guru')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Jadwal Mengajar Per Guru</h2>
                <p class="text-muted mb-0">Lihat jadwal berdasarkan guru</p>
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

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('jadwal.per-guru') }}">
                    <div class="row align-items-end">
                        <div class="col-md-10">
                            <label for="guru_id" class="form-label">Pilih Guru</label>
                            <select name="guru_id" id="guru_id" class="form-select" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach ($guru as $item)
                                    <option value="{{ $item->id }}" {{ $guruId == $item->id ? 'selected' : '' }}>
                                        {{ $item->nama }} - {{ $item->nip }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Lihat Jadwal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Jadwal Result -->
        @if ($guruId)
            @if ($jadwal->count() > 0)
                @php
                    $guruInfo = $guru->find($guruId);
                    $groupedJadwal = $jadwal->groupBy('hari');
                @endphp

                <!-- Info Guru -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                @if ($guruInfo->foto)
                                    <img src="{{ asset('storage/' . $guruInfo->foto) }}" class="rounded-circle"
                                        width="80" height="80" alt="{{ $guruInfo->nama }}">
                                @else
                                    <div class="bg-secondary rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                        style="width: 80px; height: 80px;">
                                        <i class="fas fa-user fa-2x text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-7">
                                <h4 class="mb-1">{{ $guruInfo->nama }}</h4>
                                <p class="text-muted mb-1">NIP: {{ $guruInfo->nip }}</p>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-phone me-1"></i>{{ $guruInfo->no_hp ?? '-' }}
                                    <span class="ms-3"><i
                                            class="fas fa-envelope me-1"></i>{{ $guruInfo->email ?? '-' }}</span>
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <h3 class="mb-1">{{ $jadwal->count() }}</h3>
                                <small class="text-muted">Jam Mengajar Per Minggu</small>
                                <div class="mt-2">
                                    <span class="badge bg-primary">{{ $groupedJadwal->count() }} Hari Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jadwal Per Hari -->
                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                    @if ($groupedJadwal->has($hari))
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-day me-2"></i>{{ $hari }}
                                    <span class="badge bg-light text-dark ms-2">{{ $groupedJadwal[$hari]->count() }}
                                        Pelajaran</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="20%">Waktu</th>
                                                <th width="30%">Mata Pelajaran</th>
                                                <th width="30%">Kelas</th>
                                                <th width="10%">Durasi</th>
                                                <th width="10%">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($groupedJadwal[$hari] as $item)
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold">{{ $item->jam_mulai }}</div>
                                                        <small class="text-muted">{{ $item->jam_selesai }}</small>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-info fs-6">{{ $item->mataPelajaran->nama_mapel }}</span>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-primary fs-6">{{ $item->kelas->nama_kelas }}</span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $item->durasi }} menit</small>
                                                    </td>
                                                    <td>
                                                        @if ($item->status == 'aktif')
                                                            <span class="badge bg-success">Aktif</span>
                                                        @else
                                                            <span class="badge bg-secondary">Non-Aktif</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

                <!-- Summary Statistics -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-book fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0">{{ $jadwal->unique('mapel_id')->count() }}</h4>
                                <small class="text-muted">Mata Pelajaran</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-door-open fa-2x text-success mb-2"></i>
                                <h4 class="mb-0">{{ $jadwal->unique('kelas_id')->count() }}</h4>
                                <small class="text-muted">Kelas Diajar</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                <h4 class="mb-0">{{ $jadwal->sum('durasi') }}</h4>
                                <small class="text-muted">Total Menit Per Minggu</small>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5>Tidak Ada Jadwal</h5>
                        <p class="text-muted">Guru yang dipilih belum memiliki jadwal mengajar</p>
                    </div>
                </div>
            @endif
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h5>Pilih Guru</h5>
                    <p class="text-muted">Silakan pilih guru untuk melihat jadwal mengajar</p>
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
