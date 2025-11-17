@extends('layouts.app')

@section('title', 'Guru Pengganti')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Daftar Guru Pengganti</h4>
                    <p class="text-muted mb-0">Kelola penunjukan guru pengganti</p>
                </div>
                <a href="{{ route('kurikulum.guru-pengganti.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tunjuk Guru Pengganti
                </a>
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
                                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Guru Asli</label>
                            <select name="guru_asli_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Guru</option>
                                @foreach($guru_list as $g)
                                <option value="{{ $g->id }}" {{ request('guru_asli_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- List -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Daftar Penugasan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Guru Asli</th>
                            <th>Guru Pengganti</th>
                            <th>Jadwal</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengganti as $p)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ substr($p->jadwalMengajar->jam_mulai, 0, 5) }} - {{ substr($p->jadwalMengajar->jam_selesai, 0, 5) }}</td>
                            <td>
                                <strong>{{ $p->guruAsli->nama }}</strong><br>
                                <small class="text-muted">{{ $p->guruAsli->nip }}</small>
                            </td>
                            <td>
                                <strong>{{ $p->guruPengganti->nama }}</strong><br>
                                <small class="text-muted">{{ $p->guruPengganti->nip }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $p->jadwalMengajar->kelas->nama_kelas }}</span><br>
                                <small>{{ $p->jadwalMengajar->mataPelajaran->nama_mapel }}</small>
                            </td>
                            <td>{{ $p->keterangan }}</td>
                            <td>
                                @if($p->status === 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Selesai</span>
                                @endif
                            </td>
                            <td>
                                @if($p->status === 'aktif')
                                <form action="{{ route('kurikulum.guru-pengganti.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada data guru pengganti</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pengganti->hasPages())
            <div class="mt-3">
                {{ $pengganti->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
