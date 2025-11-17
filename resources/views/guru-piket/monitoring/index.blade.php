@extends('layouts.app')

@section('title', 'Monitoring Absensi Real-time')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Monitoring Absensi Real-time</h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar-event"></i> {{ $tanggal }}
                        <button type="button" class="btn btn-sm btn-primary ms-3" onclick="refreshData()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </p>
                </div>
                <div>
                    <form method="GET" class="d-flex gap-2">
                        <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}" onchange="this.form.submit()">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4" id="stats-container">
        <div class="col-md-3">
            <div class="card stats-card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Jadwal</p>
                            <h3 class="mb-0">{{ $statistics['total_jadwal'] }}</h3>
                        </div>
                        <div class="icon-box bg-primary">
                            <i class="bi bi-calendar-week text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Sudah Absen</p>
                            <h3 class="mb-0 text-success">{{ $statistics['sudah_absen'] }}</h3>
                        </div>
                        <div class="icon-box bg-success">
                            <i class="bi bi-check-circle text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Belum Absen</p>
                            <h3 class="mb-0 text-warning">{{ $statistics['belum_absen'] }}</h3>
                        </div>
                        <div class="icon-box bg-warning">
                            <i class="bi bi-exclamation-circle text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Terlambat</p>
                            <h3 class="mb-0 text-danger">{{ $statistics['terlambat'] }}</h3>
                        </div>
                        <div class="icon-box bg-danger">
                            <i class="bi bi-clock-history text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Table -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Jadwal Hari Ini</h5>
            <span class="badge bg-info">Last Update: <span id="last-update">{{ now()->format('H:i:s') }}</span></span>
        </div>
        <div class="card-body">
            <div class="table-responsive" id="jadwal-table">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Jam</th>
                            <th>Guru</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Status Absen</th>
                            <th>Jam Absen</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal as $j)
                        <tr>
                            <td>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</td>
                            <td>
                                <strong>{{ $j->guru->nama }}</strong><br>
                                <small class="text-muted">NIP: {{ $j->guru->nip }}</small>
                            </td>
                            <td>{{ $j->kelas->nama_kelas }}</td>
                            <td>{{ $j->mataPelajaran->nama_mapel }}</td>
                            <td>
                                @php
                                    $absensi = $j->absensi->first();
                                @endphp
                                @if($absensi)
                                    @if($absensi->status === 'hadir')
                                        <span class="badge bg-success">Hadir</span>
                                    @elseif($absensi->status === 'terlambat')
                                        <span class="badge bg-warning">Terlambat</span>
                                    @elseif($absensi->status === 'izin')
                                        <span class="badge bg-info">Izin</span>
                                    @else
                                        <span class="badge bg-danger">Alpha</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Belum Absen</span>
                                @endif
                            </td>
                            <td>
                                @if($absensi)
                                    {{ substr($absensi->jam_absen, 0, 5) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('guru-piket.monitoring.detail', $j->guru_id) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada jadwal untuk hari ini</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.stats-card {
    border-left-width: 4px;
}
.icon-box {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.icon-box i {
    font-size: 24px;
}
</style>
@endpush

@push('scripts')
<script>
function refreshData() {
    const tanggal = '{{ $tanggal }}';
    
    fetch(`{{ route('guru-piket.monitoring.refresh') }}?tanggal=${tanggal}`)
        .then(response => response.json())
        .then(data => {
            // Update statistics
            document.querySelector('#stats-container .col-md-3:nth-child(1) h3').textContent = data.statistics.total_jadwal;
            document.querySelector('#stats-container .col-md-3:nth-child(2) h3').textContent = data.statistics.sudah_absen;
            document.querySelector('#stats-container .col-md-3:nth-child(3) h3').textContent = data.statistics.belum_absen;
            document.querySelector('#stats-container .col-md-3:nth-child(4) h3').textContent = data.statistics.terlambat;
            
            // Update last update time
            document.getElementById('last-update').textContent = new Date().toLocaleTimeString('id-ID');
            
            // Show success message
            alert('Data berhasil diperbarui!');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memperbarui data');
        });
}

// Auto refresh every 60 seconds
setInterval(refreshData, 60000);
</script>
@endpush
@endsection
