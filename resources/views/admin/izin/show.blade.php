@extends('layouts.app')

@section('title', 'Detail Izin/Cuti')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.izin.index') }}">Izin/Cuti</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 fw-bold">Detail Izin/Cuti</h1>
        </div>
        <a href="{{ route('admin.izin.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Izin Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">Informasi Izin</h5>
                        @if ($izin->status === 'pending')
                            <span class="badge bg-warning">
                                <i class="bi bi-clock"></i> Menunggu Persetujuan
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
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Jenis Izin</label>
                            <div class="fw-semibold">
                                <span
                                    class="badge bg-{{ $izin->jenis === 'sakit' ? 'danger' : ($izin->jenis === 'cuti' ? 'info' : 'warning') }}-subtle text-{{ $izin->jenis === 'sakit' ? 'danger' : ($izin->jenis === 'cuti' ? 'info' : 'warning') }} fs-6">
                                    {{ ucfirst($izin->jenis) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Durasi</label>
                            <div class="fw-semibold">{{ $izin->durasi_hari }} Hari</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Tanggal Mulai</label>
                            <div class="fw-semibold">{{ date('d F Y', strtotime($izin->tanggal_mulai)) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small mb-1">Tanggal Selesai</label>
                            <div class="fw-semibold">{{ date('d F Y', strtotime($izin->tanggal_selesai)) }}</div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small mb-1">Keterangan</label>
                            <div class="p-3 bg-light rounded">{{ $izin->keterangan }}</div>
                        </div>
                        @if ($izin->file_dokumen)
                            <div class="col-12">
                                <label class="text-muted small mb-1">File Pendukung</label>
                                <div>
                                    <a href="{{ Storage::url($izin->file_dokumen) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-pdf"></i> Lihat Dokumen
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Approval History -->
            @if ($izin->status !== 'pending')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">Riwayat Persetujuan</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div
                                    class="avatar-icon bg-{{ $izin->status === 'approved' ? 'success' : 'danger' }}-subtle text-{{ $izin->status === 'approved' ? 'success' : 'danger' }} rounded-circle">
                                    <i
                                        class="bi bi-{{ $izin->status === 'approved' ? 'check-circle' : 'x-circle' }} fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold">
                                    {{ $izin->status === 'approved' ? 'Disetujui' : 'Ditolak' }} oleh
                                    {{ $izin->disetujuiOleh->name ?? '-' }}
                                </div>
                                <div class="text-muted small">
                                    {{ date('d F Y H:i', strtotime($izin->tanggal_disetujui)) }}</div>
                                @if ($izin->status === 'rejected' && $izin->alasan_penolakan)
                                    <div class="mt-2 p-2 bg-danger-subtle text-danger rounded small">
                                        <strong>Alasan Penolakan:</strong><br>
                                        {{ $izin->alasan_penolakan }}
                                    </div>
                                @endif
                                @if ($izin->status === 'approved' && $izin->guruPengganti)
                                    <div class="mt-2 p-2 bg-success-subtle text-success rounded small">
                                        <strong>Guru Pengganti:</strong><br>
                                        {{ $izin->guruPengganti->user->name ?? '-' }}
                                        ({{ $izin->guruPengganti->nip ?? '-' }})
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Guru Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">Informasi Guru</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-lg bg-primary-subtle text-primary rounded-circle mx-auto mb-3">
                            <span class="fs-3 fw-bold">{{ strtoupper(substr($izin->guru->user->name, 0, 1)) }}</span>
                        </div>
                        <h5 class="mb-1 fw-bold">{{ $izin->guru->user->name }}</h5>
                        <p class="text-muted mb-0">{{ $izin->guru->nip }}</p>
                    </div>
                    <hr>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Status:</span>
                            <span class="badge bg-success-subtle text-success">{{ ucfirst($izin->guru->status) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Email:</span>
                            <span>{{ $izin->guru->user->email }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Diajukan:</span>
                            <span>{{ date('d/m/Y H:i', strtotime($izin->created_at)) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            @if ($izin->status === 'pending')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">Aksi</h5>
                    </div>
                    <div class="card-body">
                        <!-- Approve Form -->
                        <form action="{{ route('admin.izin.approve', $izin->id) }}" method="POST" id="approveForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small">Guru Pengganti (Opsional)</label>
                                <select class="form-select" name="guru_pengganti_id">
                                    <option value="">-- Pilih Guru Pengganti --</option>
                                    @foreach (\App\Models\Guru::where('status', 'aktif')->where('id', '!=', $izin->guru_id)->orderBy('nama')->get() as $guru)
                                        <option value="{{ $guru->id }}">{{ $guru->user->name }}
                                            ({{ $guru->nip }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-check-circle"></i> Setujui
                            </button>
                        </form>

                        <!-- Reject Button -->
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle"></i> Tolak
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.izin.reject', $izin->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Izin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Anda akan menolak permohonan izin dari <strong>{{ $izin->guru->user->name }}</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="alasan_penolakan" rows="4" required
                                placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Tolak Izin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .avatar-lg {
                width: 80px;
                height: 80px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

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
