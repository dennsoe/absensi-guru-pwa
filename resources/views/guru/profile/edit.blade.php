@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="{{ route('guru.profile.index') }}" class="btn btn-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <h4 class="mb-1">Edit Profil</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('guru.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Photo Preview -->
                            <div class="mb-4 text-center">
                                <div class="mb-3">
                                    <img src="{{ auth()->user()->foto_url }}" alt="{{ auth()->user()->nama }}"
                                        class="rounded-circle" width="120" height="120"
                                        style="object-fit: cover; border: 3px solid #e9ecef;" id="photoPreview">
                                </div>
                                <div class="mb-2">
                                    <label for="foto_profil" class="btn btn-sm btn-primary">
                                        <i class="bi bi-camera"></i> Pilih Foto
                                    </label>
                                    <input type="file" name="foto_profil" id="foto_profil"
                                        class="d-none @error('foto_profil') is-invalid @enderror"
                                        accept="image/jpeg,image/jpg,image/png" onchange="previewPhoto(event)">
                                </div>
                                <small class="text-muted d-block">Format: JPG, PNG. Maksimal: 2MB</small>
                                @error('foto_profil')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">NIP</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->nip }}" disabled>
                                <small class="text-muted">NIP tidak dapat diubah</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama"
                                    class="form-control @error('nama') is-invalid @enderror"
                                    value="{{ old('nama', auth()->user()->nama) }}" required>
                                @error('nama')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', auth()->user()->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">No. HP</label>
                                <input type="text" name="no_hp"
                                    class="form-control @error('no_hp') is-invalid @enderror"
                                    value="{{ old('no_hp', auth()->user()->no_hp) }}">
                                @error('no_hp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', auth()->user()->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                                <a href="{{ route('guru.profile.index') }}" class="btn btn-secondary">
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
        function previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('photoPreview');
                    const placeholder = document.getElementById('photoPlaceholder');
                    if (placeholder) {
                        placeholder.classList.add('d-none');
                    }
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
