@extends('layouts.app')

@section('title', 'Approval Jadwal')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h4 class="mb-1">Approval Jadwal Mengajar</h4>
                <p class="text-muted mb-0">Verifikasi dan setujui jadwal mengajar</p>
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
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>
                                        Approved</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>
                                        Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Guru</label>
                                <select name="guru_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua Guru</option>
                                    @foreach ($guru_list as $g)
                                        <option value="{{ $g->id }}"
                                            {{ request('guru_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tahun Ajaran</label>
                                <input type="text" name="tahun_ajaran" class="form-control"
                                    value="{{ request('tahun_ajaran', '2025/2026') }}" onchange="this.form.submit()">
                            </div>
                        </form>
                    </div>
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
                        <p class="text-muted mb-0 small">Approved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $total_rejected }}</h3>
                        <p class="text-muted mb-0 small">Rejected</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadwal List -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Daftar Jadwal</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Guru</th>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Ruangan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jadwal as $j)
                                <tr>
                                    <td>
                                        <strong>{{ $j->guru->nama }}</strong><br>
                                        <small class="text-muted">{{ $j->guru->nip }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst($j->hari) }}</span>
                                    </td>
                                    <td>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</td>
                                    <td>{{ $j->kelas->nama_kelas }}</td>
                                    <td>{{ $j->mataPelajaran->nama_mapel }}</td>
                                    <td>{{ $j->ruangan ?? '-' }}</td>
                                    <td>
                                        @if ($j->approval_status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($j->approval_status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($j->approval_status === 'pending')
                                            <div class="btn-group btn-group-sm">
                                                <form action="{{ route('kurikulum.approval.approve', $j->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success"
                                                        onclick="return confirm('Setujui jadwal ini?')">
                                                        <i class="bi bi-check-circle"></i> Approve
                                                    </button>
                                                </form>
                                                <form action="{{ route('kurikulum.approval.reject', $j->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger"
                                                        onclick="return confirm('Tolak jadwal ini?')">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada jadwal untuk di-approve</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($jadwal->hasPages())
                    <div class="mt-3">
                        {{ $jadwal->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
