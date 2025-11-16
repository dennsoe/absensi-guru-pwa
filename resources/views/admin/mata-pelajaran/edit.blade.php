@extends('layouts.app')

@section('title', 'Edit Mata Pelajaran')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Mata Pelajaran</h1>
            <p class="page-subtitle">Ubah data mata pelajaran {{ $mapel->nama_mapel }}</p>
        </div>
        <a href="{{ route('admin.mapel.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="page-section">
                <form action="{{ route('admin.mapel.update', $mapel->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Kode Mapel --}}
                    <div class="mb-3">
                        <label for="kode_mapel" class="form-label">Kode Mata Pelajaran <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('kode_mapel') is-invalid @enderror" id="kode_mapel"
                            name="kode_mapel" value="{{ old('kode_mapel', $mapel->kode_mapel) }}" required
                            placeholder="Contoh: MTK, IPA, BIN">
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
                            name="nama_mapel" value="{{ old('nama_mapel', $mapel->nama_mapel) }}" required
                            placeholder="Contoh: Matematika">
                        @error('nama_mapel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4"
                            placeholder="Deskripsi singkat tentang mata pelajaran (opsional)">{{ old('deskripsi', $mapel->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Keterangan tambahan tentang mata pelajaran</small>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Mata Pelajaran
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
                <h3 class="section-title">Informasi Mata Pelajaran</h3>
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Dibuat:</strong></td>
                            <td>{{ $mapel->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terakhir Update:</strong></td>
                            <td>{{ $mapel->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Digunakan di:</strong></td>
                            <td>{{ $mapel->jadwalMengajar->count() }} jadwal mengajar</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="page-section">
                <h3 class="section-title">Catatan</h3>
                <div class="alert-card warning">
                    <i class="bi bi-exclamation-triangle alert-card-icon"></i>
                    <div class="alert-card-body">
                        <div class="alert-card-message">
                            <ul style="margin: 0; padding-left: 20px;">
                                <li>Kode mata pelajaran harus unik</li>
                                <li>Mata pelajaran yang sudah digunakan dalam jadwal tidak dapat dihapus</li>
                                <li>Perubahan akan mempengaruhi semua jadwal terkait</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
