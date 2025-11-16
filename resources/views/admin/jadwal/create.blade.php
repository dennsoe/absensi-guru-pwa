@extends('layouts.app')

@section('title', 'Tambah Jadwal Mengajar')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Tambah Jadwal Mengajar</h1>
            <p class="page-subtitle">Form untuk menambahkan jadwal mengajar baru</p>
        </div>
        <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="page-section">
                <form action="{{ route('admin.jadwal.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        {{-- Guru --}}
                        <div class="col-md-6 mb-3">
                            <label for="guru_id" class="form-label">Guru <span class="text-danger">*</span></label>
                            <select class="form-control @error('guru_id') is-invalid @enderror" id="guru_id"
                                name="guru_id" required>
                                <option value="">-- Pilih Guru --</option>
                                @foreach ($guru_list as $guru)
                                    <option value="{{ $guru->id }}" {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama }} - {{ $guru->nip ?? 'Tanpa NIP' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('guru_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kelas --}}
                        <div class="col-md-6 mb-3">
                            <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-control @error('kelas_id') is-invalid @enderror" id="kelas_id"
                                name="kelas_id" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($kelas_list as $kelas)
                                    <option value="{{ $kelas->id }}"
                                        {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                        {{ $kelas->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Mata Pelajaran --}}
                    <div class="mb-3">
                        <label for="mapel_id" class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-control @error('mapel_id') is-invalid @enderror" id="mapel_id" name="mapel_id"
                            required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach ($mapel_list as $mapel)
                                <option value="{{ $mapel->id }}" {{ old('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->nama_mapel }} ({{ $mapel->kode_mapel }})
                                </option>
                            @endforeach
                        </select>
                        @error('mapel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- Hari --}}
                        <div class="col-md-4 mb-3">
                            <label for="hari" class="form-label">Hari <span class="text-danger">*</span></label>
                            <select class="form-control @error('hari') is-invalid @enderror" id="hari" name="hari"
                                required>
                                <option value="">-- Pilih Hari --</option>
                                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                                    <option value="{{ $hari }}" {{ old('hari') == $hari ? 'selected' : '' }}>
                                        {{ $hari }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Jam Mulai --}}
                        <div class="col-md-4 mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('jam_mulai') is-invalid @enderror"
                                id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai') }}" required>
                            @error('jam_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Jam Selesai --}}
                        <div class="col-md-4 mb-3">
                            <label for="jam_selesai" class="form-label">Jam Selesai <span
                                    class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('jam_selesai') is-invalid @enderror"
                                id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai') }}" required>
                            @error('jam_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Ruangan --}}
                    <div class="mb-3">
                        <label for="ruangan" class="form-label">Ruangan</label>
                        <input type="text" class="form-control @error('ruangan') is-invalid @enderror" id="ruangan"
                            name="ruangan" value="{{ old('ruangan') }}" placeholder="Contoh: Lab Komputer 1">
                        @error('ruangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- Tahun Ajaran --}}
                        <div class="col-md-4 mb-3">
                            <label for="tahun_ajaran" class="form-label">Tahun Ajaran <span
                                    class="text-danger">*</span></label>
                            <select class="form-control @error('tahun_ajaran') is-invalid @enderror" id="tahun_ajaran"
                                name="tahun_ajaran" required>
                                <option value="">-- Pilih Tahun --</option>
                                <option value="2024/2025" {{ old('tahun_ajaran') == '2024/2025' ? 'selected' : '' }}>
                                    2024/2025</option>
                                <option value="2025/2026" {{ old('tahun_ajaran') == '2025/2026' ? 'selected' : '' }}>
                                    2025/2026</option>
                                <option value="2026/2027" {{ old('tahun_ajaran') == '2026/2027' ? 'selected' : '' }}>
                                    2026/2027</option>
                            </select>
                            @error('tahun_ajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Semester --}}
                        <div class="col-md-4 mb-3">
                            <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select class="form-control @error('semester') is-invalid @enderror" id="semester"
                                name="semester" required>
                                <option value="">-- Pilih Semester --</option>
                                <option value="Ganjil" {{ old('semester') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ old('semester') == 'Genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status"
                                name="status" required>
                                <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif
                                </option>
                                <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Non-Aktif
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan Jadwal
                        </button>
                        <a href="{{ route('admin.jadwal.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="page-section">
                <h3 class="section-title">Panduan Pengisian</h3>
                <div class="alert-card info">
                    <i class="bi bi-info-circle alert-card-icon"></i>
                    <div class="alert-card-body">
                        <div class="alert-card-message">
                            <ul style="margin: 0; padding-left: 20px;">
                                <li>Pastikan tidak ada jadwal bentrok untuk guru yang sama</li>
                                <li>Jam selesai harus lebih besar dari jam mulai</li>
                                <li>Ruangan bersifat opsional</li>
                                <li>Status default adalah Aktif</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
