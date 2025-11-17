@extends('layouts.app')

@section('title', 'Ganti Password')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="{{ route('guru.profile.index') }}" class="btn btn-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <h4 class="mb-1">Ganti Password</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('guru.profile.update-password') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Password Lama <span class="text-danger">*</span></label>
                                <input type="password" name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                                <input type="password" name="new_password"
                                    class="form-control @error('new_password') is-invalid @enderror" required>
                                <small class="text-muted">Minimal 8 karakter</small>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Konfirmasi Password Baru <span
                                        class="text-danger">*</span></label>
                                <input type="password" name="new_password_confirmation"
                                    class="form-control @error('new_password_confirmation') is-invalid @enderror" required>
                                @error('new_password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Setelah password diubah, Anda akan diminta login kembali.
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-key"></i> Ganti Password
                                </button>
                                <a href="{{ route('guru.profile.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-shield-check"></i> Tips Keamanan</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li>Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol</li>
                            <li>Minimal 8 karakter</li>
                            <li>Jangan gunakan informasi pribadi yang mudah ditebak</li>
                            <li>Ganti password secara berkala (minimal 3 bulan sekali)</li>
                            <li>Jangan bagikan password ke orang lain</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
