@extends('layouts.app')

@section('title', 'Edit Jadwal Mengajar')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="{{ route('kurikulum.jadwal.index') }}" class="btn btn-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <h4 class="mb-1">Edit Jadwal Mengajar</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('kurikulum.jadwal.update', $jadwal->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Guru <span class="text-danger">*</span></label>
                                    <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror"
                                        required>
                                        <option value="">Pilih Guru</option>
                                        @foreach ($guru_list as $g)
                                            <option value="{{ $g->id }}"
                                                {{ old('guru_id', $jadwal->guru_id) == $g->id ? 'selected' : '' }}>
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
                                                {{ old('kelas_id', $jadwal->kelas_id) == $k->id ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }}</option>
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
                                            {{ old('mapel_id', $jadwal->mapel_id) == $m->id ? 'selected' : '' }}>
                                            {{ $m->nama_mapel }}</option>
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
                                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                                            <option value="{{ $hari }}"
                                                {{ old('hari', $jadwal->hari) === $hari ? 'selected' : '' }}>
                                                {{ $hari }}</option>
                                        @endforeach
                                    </select>
                                    @error('hari')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ruangan</label>
                                    <input type="text" name="ruangan"
                                        class="form-control @error('ruangan') is-invalid @enderror"
                                        value="{{ old('ruangan', $jadwal->ruangan) }}">
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
                                        value="{{ old('jam_mulai', substr($jadwal->jam_mulai, 0, 5)) }}" required>
                                    @error('jam_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_selesai"
                                        class="form-control @error('jam_selesai') is-invalid @enderror"
                                        value="{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}" required>
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
                                        value="{{ old('tahun_ajaran', $jadwal->tahun_ajaran) }}" required>
                                    @error('tahun_ajaran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                                    <select name="semester" class="form-select @error('semester') is-invalid @enderror"
                                        required>
                                        <option value="Ganjil"
                                            {{ old('semester', $jadwal->semester) == 'Ganjil' ? 'selected' : '' }}>Semester Ganjil
                                        </option>
                                        <option value="Genap"
                                            {{ old('semester', $jadwal->semester) == 'Genap' ? 'selected' : '' }}>Semester Genap
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
                                    <option value="aktif"
                                        {{ old('status', $jadwal->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif"
                                        {{ old('status', $jadwal->status) === 'nonaktif' ? 'selected' : '' }}>Non-Aktif
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update
                                </button>
                                <a href="{{ route('kurikulum.jadwal.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
