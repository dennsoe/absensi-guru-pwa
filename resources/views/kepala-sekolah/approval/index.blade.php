@extends('layouts.app')

@section('title', 'Approval Izin/Cuti')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h4 class="mb-1">Approval Izin/Cuti Guru</h4>
            <p class="text-muted mb-0">Kelola persetujuan izin dan cuti guru</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select" onchange="this.form.submit()">
                                @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ ($bulan ?? now()->month) == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $i)->locale('id')->translatedFormat('F') }}
                                </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select" onchange="this.form.submit()">
                                @for($i = date('Y'); $i >= date('Y') - 2; $i--)
                                <option value="{{ $i }}" {{ ($tahun ?? now()->year) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Izin/Cuti Table -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Daftar Permohonan Izin/Cuti</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <th>Guru</th>
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
                                <strong>{{ $i->guru->nama }}</strong><br>
                                <small class="text-muted">{{ $i->guru->nip }}</small>
                            </td>
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
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('kepala-sekolah.approval.show', $i->id) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data izin/cuti</td>
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
