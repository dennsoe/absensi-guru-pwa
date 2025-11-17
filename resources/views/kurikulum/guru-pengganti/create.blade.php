@extends('layouts.app')

@section('title', 'Tunjuk Guru Pengganti')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="{{ route('kurikulum.guru-pengganti.index') }}" class="btn btn-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <h4 class="mb-1">Tunjuk Guru Pengganti</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('kurikulum.guru-pengganti.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Jadwal Mengajar <span class="text-danger">*</span></label>
                                <select name="jadwal_mengajar_id"
                                    class="form-select @error('jadwal_mengajar_id') is-invalid @enderror" required
                                    id="jadwalSelect">
                                    <option value="">Pilih Jadwal</option>
                                    @foreach ($jadwal_list as $j)
                                        <option value="{{ $j->id }}" data-guru-id="{{ $j->guru_id }}"
                                            data-guru-nama="{{ $j->guru->nama }}">
                                            {{ ucfirst($j->hari) }} |
                                            {{ substr($j->jam_mulai, 0, 5) }}-{{ substr($j->jam_selesai, 0, 5) }} |
                                            {{ $j->kelas->nama_kelas }} | {{ $j->mataPelajaran->nama_mapel }} |
                                            Guru: {{ $j->guru->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jadwal_mengajar_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal Penggantian <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal"
                                    class="form-control @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal') }}" required>
                                @error('tanggal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Guru Asli</label>
                                <input type="text" class="form-control" id="guruAsliDisplay" readonly
                                    placeholder="Pilih jadwal terlebih dahulu">
                                <input type="hidden" name="guru_asli_id" id="guruAsliId">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Guru Pengganti <span class="text-danger">*</span></label>
                                <select name="guru_pengganti_id"
                                    class="form-select @error('guru_pengganti_id') is-invalid @enderror" required>
                                    <option value="">Pilih Guru Pengganti</option>
                                    @foreach ($guru_list as $g)
                                        <option value="{{ $g->id }}"
                                            {{ old('guru_pengganti_id') == $g->id ? 'selected' : '' }}>
                                            {{ $g->nama }} ({{ $g->nip }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('guru_pengganti_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                                <textarea name="keterangan" rows="3" class="form-control @error('keterangan') is-invalid @enderror" required>{{ old('keterangan') }}</textarea>
                                <small class="text-muted">Alasan penggantian guru (misal: izin, sakit, dinas luar)</small>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Guru pengganti akan menerima notifikasi penugasan.
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan
                                </button>
                                <a href="{{ route('kurikulum.guru-pengganti.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('jadwalSelect').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const guruId = selected.getAttribute('data-guru-id');
            const guruNama = selected.getAttribute('data-guru-nama');

            if (guruId && guruNama) {
                document.getElementById('guruAsliId').value = guruId;
                document.getElementById('guruAsliDisplay').value = guruNama;
            } else {
                document.getElementById('guruAsliId').value = '';
                document.getElementById('guruAsliDisplay').value = '';
            }
        });
    </script>
@endsection
