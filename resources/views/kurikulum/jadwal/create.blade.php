@extends('layouts.app')

@section('title', 'Tambah Jadwal Mengajar')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="{{ route('kurikulum.jadwal.index') }}" class="btn btn-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <h4 class="mb-1">Tambah Jadwal Mengajar</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('kurikulum.jadwal.store') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Guru <span class="text-danger">*</span></label>
                                    <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror"
                                        required>
                                        <option value="">Pilih Guru</option>
                                        @foreach ($guru_list as $g)
                                            <option value="{{ $g->id }}"
                                                {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama }} ({{ $g->nip }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('guru_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                    <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror"
                                        required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach ($kelas_list as $k)
                                            <option value="{{ $k->id }}"
                                                {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                                <select name="mapel_id" class="form-select @error('mapel_id') is-invalid @enderror"
                                    required>
                                    <option value="">Pilih Mata Pelajaran</option>
                                    @foreach ($mapel_list as $m)
                                        <option value="{{ $m->id }}"
                                            {{ old('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama_mapel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('mapel_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Hari <span class="text-danger">*</span></label>
                                    <select name="hari" class="form-select @error('hari') is-invalid @enderror" required>
                                        <option value="">Pilih Hari</option>
                                        <option value="Senin" {{ old('hari') === 'Senin' ? 'selected' : '' }}>Senin
                                        </option>
                                        <option value="Selasa" {{ old('hari') === 'Selasa' ? 'selected' : '' }}>Selasa
                                        </option>
                                        <option value="Rabu" {{ old('hari') === 'Rabu' ? 'selected' : '' }}>Rabu</option>
                                        <option value="Kamis" {{ old('hari') === 'Kamis' ? 'selected' : '' }}>Kamis
                                        </option>
                                        <option value="Jumat" {{ old('hari') === 'Jumat' ? 'selected' : '' }}>Jumat
                                        </option>
                                    </select>
                                    @error('hari')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ruangan</label>
                                    <input type="text" name="ruangan"
                                        class="form-control @error('ruangan') is-invalid @enderror"
                                        value="{{ old('ruangan') }}" placeholder="Contoh: Lab Komputer">
                                    @error('ruangan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_mulai"
                                        class="form-control @error('jam_mulai') is-invalid @enderror"
                                        value="{{ old('jam_mulai') }}" required>
                                    @error('jam_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_selesai"
                                        class="form-control @error('jam_selesai') is-invalid @enderror"
                                        value="{{ old('jam_selesai') }}" required>
                                    @error('jam_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                    <input type="text" name="tahun_ajaran"
                                        class="form-control @error('tahun_ajaran') is-invalid @enderror"
                                        value="{{ old('tahun_ajaran', '2025/2026') }}" required>
                                    @error('tahun_ajaran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                                    <select name="semester" class="form-select @error('semester') is-invalid @enderror"
                                        required>
                                        <option value="Ganjil" {{ old('semester') == 'Ganjil' ? 'selected' : '' }}>Semester Ganjil
                                        </option>
                                        <option value="Genap" {{ old('semester') == 'Genap' ? 'selected' : '' }}>Semester Genap
                                        </option>
                                    </select>
                                    @error('semester')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif
                                    </option>
                                    <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>
                                        Non-Aktif</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan
                                </button>
                                <a href="{{ route('kurikulum.jadwal.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Informasi</h6>
                    <p class="mb-0 small">Sistem akan otomatis mendeteksi konflik jadwal. Pastikan tidak ada guru yang
                        mengajar pada hari dan jam yang sama.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
