@extends('layouts.app')

@section('title', 'Tambah Kelas')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Tambah Kelas Baru</h1>
            <p class="page-subtitle">Form untuk menambahkan data kelas baru</p>
        </div>
        <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="page-section">
                <form action="{{ route('admin.kelas.store') }}" method="POST">
                    @csrf

                    {{-- Nama Kelas --}}
                    <div class="mb-3">
                        <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror" id="nama_kelas"
                            name="nama_kelas" value="{{ old('nama_kelas') }}" required placeholder="Contoh: X-IPA-1">
                        @error('nama_kelas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Format: [Tingkat]-[Jurusan]-[Nomor]</small>
                    </div>

                    {{-- Tingkat --}}
                    <div class="mb-3">
                        <label for="tingkat" class="form-label">Tingkat <span class="text-danger">*</span></label>
                        <select class="form-control @error('tingkat') is-invalid @enderror" id="tingkat" name="tingkat"
                            required>
                            <option value="">-- Pilih Tingkat --</option>
                            <option value="10" {{ old('tingkat') == '10' ? 'selected' : '' }}>Kelas 10</option>
                            <option value="11" {{ old('tingkat') == '11' ? 'selected' : '' }}>Kelas 11</option>
                            <option value="12" {{ old('tingkat') == '12' ? 'selected' : '' }}>Kelas 12</option>
                        </select>
                        @error('tingkat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Jurusan --}}
                    <div class="mb-3">
                        <label for="jurusan" class="form-label">Jurusan</label>
                        <input type="text" class="form-control @error('jurusan') is-invalid @enderror" id="jurusan"
                            name="jurusan" value="{{ old('jurusan') }}" placeholder="Contoh: IPA, IPS, atau kosongkan">
                        @error('jurusan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="mb-3">
                        <label for="tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select class="form-control @error('tahun_ajaran') is-invalid @enderror" id="tahun_ajaran"
                            name="tahun_ajaran" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            <option value="2024/2025" {{ old('tahun_ajaran') == '2024/2025' ? 'selected' : '' }}>2024/2025
                            </option>
                            <option value="2025/2026" {{ old('tahun_ajaran') == '2025/2026' ? 'selected' : '' }}>2025/2026
                            </option>
                            <option value="2026/2027" {{ old('tahun_ajaran') == '2026/2027' ? 'selected' : '' }}>2026/2027
                            </option>
                        </select>
                        @error('tahun_ajaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    {{-- Wali Kelas --}}
                    <div class="mb-3">
                        <label for="wali_kelas_id" class="form-label">Wali Kelas</label>
                        <select class="form-control @error('wali_kelas_id') is-invalid @enderror" id="wali_kelas_id"
                            name="wali_kelas_id">
                            <option value="">-- Pilih Wali Kelas --</option>
                            @foreach ($guru_list as $guru)
                                <option value="{{ $guru->id }}"
                                    {{ old('wali_kelas_id') == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->nama }} - {{ $guru->nip ?? 'Tanpa NIP' }}
                                </option>
                            @endforeach
                        </select>
                        @error('wali_kelas_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Opsional, dapat diisi nanti</small>
                    </div>

                    {{-- Ketua Kelas --}}
                    <div class="mb-3">
                        <label for="ketua_kelas_user_id" class="form-label">Ketua Kelas (Siswa)</label>
                        <select class="form-control @error('ketua_kelas_user_id') is-invalid @enderror"
                            id="ketua_kelas_user_id" name="ketua_kelas_user_id">
                            <option value="">-- Pilih Ketua Kelas --</option>
                            @foreach ($ketua_kelas_list as $siswa)
                                <option value="{{ $siswa->id }}"
                                    {{ old('ketua_kelas_user_id') == $siswa->id ? 'selected' : '' }}>
                                    {{ $siswa->nama }} - {{ $siswa->username }}
                                </option>
                            @endforeach
                        </select>
                        @error('ketua_kelas_user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Hanya menampilkan siswa yang belum ditugaskan ke kelas
                            lain</small>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan Kelas
                        </button>
                        <a href="{{ route('admin.kelas.index') }}" class="btn btn-secondary">
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
                                <li>Nama kelas harus unik</li>
                                <li>Tingkat: 10, 11, atau 12</li>
                                <li>Jurusan bersifat opsional</li>
                                <li>Wali kelas dapat diisi nanti</li>
                                <li>Ketua kelas adalah siswa yang bertanggung jawab di kelas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
