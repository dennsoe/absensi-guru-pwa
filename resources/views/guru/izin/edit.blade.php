@extends('layouts.app')

@section('title', 'Edit Permohonan Izin/Cuti')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="{{ route('guru.izin.index') }}" class="btn btn-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <h4 class="mb-1">Edit Permohonan</h4>
            </div>
        </div>

        @if ($izin->status !== 'pending')
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Permohonan yang sudah disetujui/ditolak tidak dapat diedit.
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        @if ($izin->status === 'pending')
                            <form action="{{ route('guru.izin.update', $izin->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label">Jenis Permohonan <span class="text-danger">*</span></label>
                                    <select name="jenis" class="form-select @error('jenis') is-invalid @enderror"
                                        required>
                                        <option value="">Pilih Jenis</option>
                                        <option value="izin"
                                            {{ old('jenis', $izin->jenis) === 'izin' ? 'selected' : '' }}>Izin</option>
                                        <option value="cuti"
                                            {{ old('jenis', $izin->jenis) === 'cuti' ? 'selected' : '' }}>Cuti</option>
                                        <option value="sakit"
                                            {{ old('jenis', $izin->jenis) === 'sakit' ? 'selected' : '' }}>Sakit</option>
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
                                            value="{{ old('tanggal_mulai', $izin->tanggal_mulai) }}" required>
                                        @error('tanggal_mulai')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_selesai"
                                            class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                            value="{{ old('tanggal_selesai', $izin->tanggal_selesai) }}" required>
                                        @error('tanggal_selesai')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Alasan <span class="text-danger">*</span></label>
                                    <textarea name="alasan" rows="4" class="form-control @error('alasan') is-invalid @enderror" required>{{ old('alasan', $izin->alasan) }}</textarea>
                                    @error('alasan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if ($izin->file_pendukung)
                                    <div class="mb-3">
                                        <label class="form-label">File Pendukung Saat Ini</label>
                                        <div>
                                            <a href="{{ Storage::url($izin->file_pendukung) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-earmark"></i> Lihat File
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Upload File Baru (Optional)</label>
                                    <input type="file" name="file_pendukung"
                                        class="form-control @error('file_pendukung') is-invalid @enderror"
                                        accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Format: PDF, JPG, PNG. Max: 2MB. Kosongkan jika tidak ingin
                                        mengganti file.</small>
                                    @error('file_pendukung')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Update
                                    </button>
                                    <a href="{{ route('guru.izin.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Batal
                                    </a>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
