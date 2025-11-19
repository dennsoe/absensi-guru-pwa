@extends('layouts.app')

@section('title', 'Manajemen Izin/Cuti')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Manajemen Izin/Cuti</h1>
            <p class="text-muted mb-0">Kelola permohonan izin dan cuti guru</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-primary-subtle text-primary rounded-3">
                                <i class="bi bi-file-earmark-text fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Total Izin</div>
                            <div class="h4 mb-0 fw-bold">{{ $totalIzin }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-warning-subtle text-warning rounded-3">
                                <i class="bi bi-clock fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Pending</div>
                            <div class="h4 mb-0 fw-bold">{{ $pending }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-success-subtle text-success rounded-3">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Disetujui</div>
                            <div class="h4 mb-0 fw-bold">{{ $approved }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-icon bg-danger-subtle text-danger rounded-3">
                                <i class="bi bi-x-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Ditolak</div>
                            <div class="h4 mb-0 fw-bold">{{ $rejected }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.izin.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Cari Guru</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                            placeholder="Nama guru...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui
                            </option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Jenis</label>
                        <select class="form-select" name="jenis">
                            <option value="">Semua Jenis</option>
                            <option value="sakit" {{ request('jenis') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="izin" {{ request('jenis') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="cuti" {{ request('jenis') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="tanggal_mulai"
                            value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Tanggal Selesai</label>
                        <input type="date" class="form-control" name="tanggal_selesai"
                            value="{{ request('tanggal_selesai') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Izin List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Guru</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($izins as $izin)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $izin->guru->user->name }}</div>
                                    <div class="text-muted small">{{ $izin->guru->nip }}</div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $izin->jenis === 'sakit' ? 'danger' : ($izin->jenis === 'cuti' ? 'info' : 'warning') }}-subtle text-{{ $izin->jenis === 'sakit' ? 'danger' : ($izin->jenis === 'cuti' ? 'info' : 'warning') }}">
                                        {{ ucfirst($izin->jenis) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small">{{ date('d/m/Y', strtotime($izin->tanggal_mulai)) }}</div>
                                    <div class="small text-muted">s/d
                                        {{ date('d/m/Y', strtotime($izin->tanggal_selesai)) }}</div>
                                </td>
                                <td>{{ $izin->durasi_hari }} hari</td>
                                <td>
                                    @if ($izin->status === 'pending')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> Pending
                                        </span>
                                    @elseif($izin->status === 'approved')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Disetujui
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('admin.izin.show', $izin->id) }}"
                                        class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if ($izin->status === 'pending')
                                        <form action="{{ route('admin.izin.destroy', $izin->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Yakin ingin menghapus izin ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">Tidak ada data izin/cuti</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($izins->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $izins->links() }}
            </div>
        @endif
    </div>
    </div>

    @push('styles')
        <style>
            .avatar-icon {
                width: 48px;
                height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        </style>
    @endpush
@endsection
