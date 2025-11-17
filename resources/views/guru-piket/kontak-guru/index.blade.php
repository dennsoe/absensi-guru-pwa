@extends('layouts.app')

@section('title', 'Kontak Guru')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Kontak Guru</h4>
                        <p class="text-muted mb-0">Direktori kontak guru untuk keperluan piket</p>
                    </div>
                    <a href="{{ route('guru-piket.kontak-guru.export') }}" class="btn btn-primary" target="_blank">
                        <i class="bi bi-printer"></i> Cetak
                    </a>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cari Guru</label>
                                <input type="text" name="search" class="form-control"
                                    placeholder="Nama, NIP, atau No. Telepon" value="{{ $search ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="aktif" {{ ($status ?? '') === 'aktif' ? 'selected' : '' }}>Aktif
                                    </option>
                                    <option value="nonaktif" {{ ($status ?? '') === 'nonaktif' ? 'selected' : '' }}>
                                        Non-Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                                <a href="{{ route('guru-piket.kontak-guru.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guru List -->
        <div class="row">
            @forelse($guru as $g)
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">{{ $g->nama }}</h5>
                                    <p class="text-muted mb-0 small">NIP: {{ $g->nip }}</p>
                                </div>
                                <span class="badge bg-{{ $g->status === 'aktif' ? 'success' : 'danger' }}">
                                    {{ strtoupper($g->status) }}
                                </span>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <p class="mb-2">
                                        <i class="bi bi-envelope text-primary"></i>
                                        <small>{{ $g->user->email }}</small>
                                    </p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-2">
                                        <i class="bi bi-telephone text-success"></i>
                                        <small>{{ $g->no_telepon ?? '-' }}</small>
                                    </p>
                                </div>
                            </div>

                            @if ($g->alamat)
                                <p class="mb-3 small">
                                    <i class="bi bi-geo-alt text-danger"></i>
                                    {{ $g->alamat }}
                                </p>
                            @endif

                            <!-- Jadwal Mengajar -->
                            <div class="mb-3">
                                <p class="mb-2 small fw-bold">Jadwal Mengajar:</p>
                                @if ($g->jadwalMengajar->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($g->jadwalMengajar->take(3) as $jadwal)
                                            <span class="badge bg-info">
                                                {{ $jadwal->kelas->nama_kelas }} -
                                                {{ $jadwal->mataPelajaran->kode_mapel }}
                                            </span>
                                        @endforeach
                                        @if ($g->jadwalMengajar->count() > 3)
                                            <span class="badge bg-secondary">+{{ $g->jadwalMengajar->count() - 3 }}
                                                lainnya</span>
                                        @endif
                                    </div>
                                @else
                                    <small class="text-muted">Tidak ada jadwal</small>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('guru-piket.kontak-guru.show', $g->id) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                @if ($g->no_telepon)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $g->no_telepon) }}"
                                        class="btn btn-sm btn-success" target="_blank">
                                        <i class="bi bi-whatsapp"></i> WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> Tidak ada data guru yang ditemukan
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($guru->hasPages())
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex justify-content-center">
                        {{ $guru->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
