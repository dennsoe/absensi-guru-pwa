@extends('layouts.app')

@section('title', 'Jadwal Per Kelas')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Jadwal Mengajar Per Kelas</h2>
                <p class="text-muted mb-0">Lihat jadwal berdasarkan kelas</p>
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
                <form method="GET" action="{{ route('jadwal.per-kelas') }}">
                    <div class="row align-items-end">
                        <div class="col-md-10">
                            <label for="kelas_id" class="form-label">Pilih Kelas</label>
                            <select name="kelas_id" id="kelas_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($kelas as $item)
                                    <option value="{{ $item->id }}" {{ $kelasId == $item->id ? 'selected' : '' }}>
                                        {{ $item->nama_kelas }}
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
        @if ($kelasId)
            @if ($jadwal->count() > 0)
                @php
                    $kelasInfo = $kelas->find($kelasId);
                    $groupedJadwal = $jadwal->groupBy('hari');
                @endphp

                <!-- Info Kelas -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-1">{{ $kelasInfo->nama_kelas }}</h4>
                                <p class="text-muted mb-0">
                                    Total: {{ $jadwal->count() }} jam pelajaran per minggu
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge bg-primary fs-6">{{ $groupedJadwal->count() }} Hari Aktif</span>
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
                                                <th width="35%">Guru</th>
                                                <th width="35%">Mata Pelajaran</th>
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
                                                        <div class="d-flex align-items-center">
                                                            @if ($item->guru->foto)
                                                                <img src="{{ asset('storage/' . $item->guru->foto) }}"
                                                                    class="rounded-circle me-2" width="35"
                                                                    height="35" alt="{{ $item->guru->nama }}">
                                                            @else
                                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                                    style="width: 35px; height: 35px;">
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
                                                        <span
                                                            class="badge bg-info">{{ $item->mataPelajaran->nama_mapel }}</span>
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
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h5>Tidak Ada Jadwal</h5>
                        <p class="text-muted">Kelas yang dipilih belum memiliki jadwal mengajar</p>
                    </div>
                </div>
            @endif
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h5>Pilih Kelas</h5>
                    <p class="text-muted">Silakan pilih kelas untuk melihat jadwal mengajar</p>
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
    </style>
@endpush
