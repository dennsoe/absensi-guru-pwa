@extends('layouts.app')

@section('title', 'Ajukan Izin/Cuti')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="{{ route('guru.izin.index') }}" class="btn btn-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <h4 class="mb-1">Ajukan Izin/Cuti Baru</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('guru.izin.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Jenis Permohonan <span class="text-danger">*</span></label>
                                <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="izin" {{ old('jenis') === 'izin' ? 'selected' : '' }}>Izin</option>
                                    <option value="cuti" {{ old('jenis') === 'cuti' ? 'selected' : '' }}>Cuti</option>
                                    <option value="sakit" {{ old('jenis') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                </select>
                                @error('jenis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_mulai"
                                        class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                        value="{{ old('tanggal_mulai') }}" required>
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_selesai"
                                        class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                        value="{{ old('tanggal_selesai') }}" required>
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alasan <span class="text-danger">*</span></label>
                                <textarea name="alasan" rows="4" class="form-control @error('alasan') is-invalid @enderror" required>{{ old('alasan') }}</textarea>
                                @error('alasan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">File Pendukung (Optional)</label>
                                <input type="file" name="file_pendukung"
                                    class="form-control @error('file_pendukung') is-invalid @enderror"
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Format: PDF, JPG, PNG. Max: 2MB</small>
                                @error('file_pendukung')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Permohonan akan dikirim ke Kepala Sekolah untuk disetujui.
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Kirim Permohonan
                                </button>
                                <a href="{{ route('guru.izin.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi</h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-2"><strong>Ketentuan:</strong></p>
                        <ul class="small">
                            <li>Ajukan minimal 3 hari sebelumnya (kecuali sakit mendadak)</li>
                            <li>Lampirkan surat dokter untuk sakit > 2 hari</li>
                            <li>Cuti tahunan maksimal 12 hari/tahun</li>
                            <li>Permohonan akan diproses maksimal 2x24 jam</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
