@extends('layouts.app')

@section('title', 'Izin/Cuti Saya')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Riwayat Izin/Cuti</h4>
                    <p class="text-muted mb-0">Kelola permohonan izin dan cuti Anda</p>
                </div>
                <a href="{{ route('guru.izin.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajukan Izin/Cuti
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $total_pending }}</h3>
                    <p class="text-muted mb-0 small">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $total_approved }}</h3>
                    <p class="text-muted mb-0 small">Disetujui</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $total_rejected }}</h3>
                    <p class="text-muted mb-0 small">Ditolak</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jenis</label>
                            <select name="jenis" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Jenis</option>
                                <option value="izin" {{ request('jenis') === 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="cuti" {{ request('jenis') === 'cuti' ? 'selected' : '' }}>Cuti</option>
                                <option value="sakit" {{ request('jenis') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Izin List -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Daftar Permohonan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <th>Jenis</th>
                            <th>Periode</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($izin as $i)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($i->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $i->jenis === 'cuti' ? 'primary' : ($i->jenis === 'sakit' ? 'warning' : 'info') }}">
                                    {{ strtoupper($i->jenis) }}
                                </span>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($i->tanggal_mulai)->format('d/m/Y') }} - 
                                {{ \Carbon\Carbon::parse($i->tanggal_selesai)->format('d/m/Y') }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($i->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($i->tanggal_selesai)) + 1 }} hari
                            </td>
                            <td>
                                @if($i->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($i->status === 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                @else
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('guru.izin.show', $i->id) }}" class="btn btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($i->status === 'pending')
                                    <a href="{{ route('guru.izin.edit', $i->id) }}" class="btn btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('guru.izin.destroy', $i->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada permohonan izin/cuti</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($izin->hasPages())
            <div class="mt-3">
                {{ $izin->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
