@extends('layouts.app')

@section('title', 'Detail Permohonan Izin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('kepala-sekolah.approval.index') }}" class="btn btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h4 class="mb-1">Detail Permohonan Izin/Cuti</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Main Detail Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Permohonan</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Jenis:</strong></div>
                        <div class="col-md-9">
                            <span class="badge bg-{{ $izin->jenis === 'cuti' ? 'primary' : ($izin->jenis === 'sakit' ? 'warning' : 'info') }} fs-6">
                                {{ strtoupper($izin->jenis) }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Tanggal Mulai:</strong></div>
                        <div class="col-md-9">{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d F Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Tanggal Selesai:</strong></div>
                        <div class="col-md-9">{{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d F Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Durasi:</strong></div>
                        <div class="col-md-9">
                            {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($izin->tanggal_selesai)) + 1 }} hari
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Alasan:</strong></div>
                        <div class="col-md-9">{{ $izin->alasan }}</div>
                    </div>
                    @if($izin->file_pendukung)
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>File Pendukung:</strong></div>
                        <div class="col-md-9">
                            <a href="{{ Storage::url($izin->file_pendukung) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="bi bi-file-earmark"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Status:</strong></div>
                        <div class="col-md-9">
                            @if($izin->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($izin->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </div>
                    </div>
                    @if($izin->status !== 'pending')
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Disetujui/Ditolak oleh:</strong></div>
                        <div class="col-md-9">{{ $izin->approvedBy->name ?? '-' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Tanggal Approval:</strong></div>
                        <div class="col-md-9">{{ $izin->approved_at ? \Carbon\Carbon::parse($izin->approved_at)->format('d F Y H:i') : '-' }}</div>
                    </div>
                    @endif
                    @if($izin->catatan_approval)
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Catatan:</strong></div>
                        <div class="col-md-9">{{ $izin->catatan_approval }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Riwayat Absensi Guru -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Riwayat Absensi Guru (30 Hari Terakhir)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kelas</th>
                                    <th>Mapel</th>
                                    <th>Status</th>
                                    <th>Jam</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayat_absensi as $absen)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($absen->tanggal)->format('d/m/Y') }}</td>
                                    <td>{{ $absen->jadwalMengajar->kelas->nama_kelas }}</td>
                                    <td>{{ $absen->jadwalMengajar->mataPelajaran->nama_mapel }}</td>
                                    <td>
                                        @if($absen->status === 'hadir')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($absen->status === 'terlambat')
                                            <span class="badge bg-warning">Terlambat</span>
                                        @elseif($absen->status === 'izin')
                                            <span class="badge bg-info">Izin</span>
                                        @else
                                            <span class="badge bg-danger">Alpha</span>
                                        @endif
                                    </td>
                                    <td>{{ substr($absen->jam_absen, 0, 5) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada riwayat absensi</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Guru Info -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informasi Guru</h5>
                </div>
                <div class="card-body">
                    <h5>{{ $izin->guru->nama }}</h5>
                    <p class="mb-2"><strong>NIP:</strong> {{ $izin->guru->nip }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $izin->guru->user->email }}</p>
                    <p class="mb-0"><strong>Telepon:</strong> {{ $izin->guru->no_telepon ?? '-' }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            @if($izin->status === 'pending')
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('kepala-sekolah.approval.approve', $izin->id) }}" method="POST" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Catatan (Opsional)</label>
                            <textarea name="catatan_approval" class="form-control" rows="3" placeholder="Tambahkan catatan jika perlu..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Setujui
                        </button>
                    </form>

                    <form action="{{ route('kepala-sekolah.approval.reject', $izin->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak permohonan ini?')">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="alasan_penolakan" class="form-control" rows="3" placeholder="Masukkan alasan penolakan..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle"></i> Tolak
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
