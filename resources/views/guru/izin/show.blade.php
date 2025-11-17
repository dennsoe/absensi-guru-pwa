@extends('layouts.app')

@section('title', 'Detail Permohonan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('guru.izin.index') }}" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h4 class="mb-1">Detail Permohonan Izin/Cuti</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Status Info -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">Status Permohonan</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            @if($izin->status === 'pending')
                                <span class="badge bg-warning fs-6">PENDING</span>
                            @elseif($izin->status === 'approved')
                                <span class="badge bg-success fs-6">DISETUJUI</span>
                            @else
                                <span class="badge bg-danger fs-6">DITOLAK</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Permohonan -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Permohonan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Jenis</th>
                            <td>
                                <span class="badge bg-{{ $izin->jenis === 'cuti' ? 'primary' : ($izin->jenis === 'sakit' ? 'warning' : 'info') }}">
                                    {{ strtoupper($izin->jenis) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai</th>
                            <td>{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Selesai</th>
                            <td>{{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Durasi</th>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($izin->tanggal_selesai)) + 1 }} hari</strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Alasan</th>
                            <td>{{ $izin->alasan }}</td>
                        </tr>
                        @if($izin->file_pendukung)
                        <tr>
                            <th>File Pendukung</th>
                            <td>
                                <a href="{{ Storage::url($izin->file_pendukung) }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="bi bi-download"></i> Unduh File
                                </a>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <td>{{ \Carbon\Carbon::parse($izin->created_at)->format('d F Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Response from Kepala Sekolah -->
            @if($izin->status !== 'pending' && $izin->catatan_approval)
            <div class="card mt-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Catatan dari Kepala Sekolah</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">{{ $izin->catatan_approval }}</p>
                    <small class="text-muted">
                        Diproses pada: {{ \Carbon\Carbon::parse($izin->tanggal_approval)->format('d F Y H:i') }}
                    </small>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Actions -->
            @if($izin->status === 'pending')
            <div class="card mb-3">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0"><i class="bi bi-hourglass-split"></i> Menunggu Approval</h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">Permohonan Anda sedang diproses oleh Kepala Sekolah.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('guru.izin.edit', $izin->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <form action="{{ route('guru.izin.destroy', $izin->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan permohonan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="bi bi-x-circle"></i> Batalkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            <!-- Timeline Info -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-clock-history"></i> Timeline</h6>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        <li class="mb-3">
                            <small class="text-muted">{{ \Carbon\Carbon::parse($izin->created_at)->format('d/m/Y H:i') }}</small>
                            <p class="mb-0 small"><strong>Permohonan diajukan</strong></p>
                        </li>
                        @if($izin->status !== 'pending')
                        <li>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($izin->tanggal_approval)->format('d/m/Y H:i') }}</small>
                            <p class="mb-0 small">
                                <strong>
                                    @if($izin->status === 'approved')
                                        Permohonan disetujui
                                    @else
                                        Permohonan ditolak
                                    @endif
                                </strong>
                            </p>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
