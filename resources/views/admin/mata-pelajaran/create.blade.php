@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Tambah Mata Pelajaran</h1>
            <p class="page-subtitle">Form untuk menambahkan mata pelajaran baru</p>
        </div>
        <a href="{{ route('admin.mapel.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="page-section">
                <form action="{{ route('admin.mapel.store') }}" method="POST">
                    @csrf

                    {{-- Kode Mapel --}}
                    <div class="mb-3">
                        <label for="kode_mapel" class="form-label">Kode Mata Pelajaran <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('kode_mapel') is-invalid @enderror" id="kode_mapel"
                            name="kode_mapel" value="{{ old('kode_mapel') }}" required placeholder="Contoh: MTK, IPA, BIN">
                        @error('kode_mapel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Singkatan unik untuk mata pelajaran (maksimal 10
                            karakter)</small>
                    </div>

                    {{-- Nama Mapel --}}
                    <div class="mb-3">
                        <label for="nama_mapel" class="form-label">Nama Mata Pelajaran <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_mapel') is-invalid @enderror" id="nama_mapel"
                            name="nama_mapel" value="{{ old('nama_mapel') }}" required placeholder="Contoh: Matematika">
                        @error('nama_mapel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4"
                            placeholder="Deskripsi singkat tentang mata pelajaran (opsional)">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Keterangan tambahan tentang mata pelajaran</small>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan Mata Pelajaran
                        </button>
                        <a href="{{ route('admin.mapel.index') }}" class="btn btn-secondary">
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
                                <li>Kode mata pelajaran harus unik</li>
                                <li>Gunakan singkatan yang mudah diingat</li>
                                <li>Nama mata pelajaran wajib diisi</li>
                                <li>Deskripsi bersifat opsional</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-section">
                <h3 class="section-title">Contoh Kode Mapel</h3>
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>MTK</strong></td>
                            <td>Matematika</td>
                        </tr>
                        <tr>
                            <td><strong>BIN</strong></td>
                            <td>Bahasa Indonesia</td>
                        </tr>
                        <tr>
                            <td><strong>BING</strong></td>
                            <td>Bahasa Inggris</td>
                        </tr>
                        <tr>
                            <td><strong>FIS</strong></td>
                            <td>Fisika</td>
                        </tr>
                        <tr>
                            <td><strong>KIM</strong></td>
                            <td>Kimia</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
