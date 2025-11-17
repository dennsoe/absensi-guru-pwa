@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit User</h1>
            <p class="page-subtitle">Ubah data akun {{ $user->nama }}</p>
        </div>
        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="page-section">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Foto Profil --}}
                    <div class="mb-4 text-center">
                        <div class="mb-3">
                            <img src="{{ $user->foto_url }}" alt="{{ $user->nama }}" class="rounded-circle" width="120"
                                height="120" style="object-fit: cover; border: 3px solid #e9ecef;" id="fotoPreview">
                        </div>
                        <div class="mb-2">
                            <label for="foto_profil" class="btn btn-sm btn-primary">
                                <i class="bi bi-camera"></i> Ubah Foto
                            </label>
                            <input type="file" name="foto_profil" id="foto_profil"
                                class="d-none @error('foto_profil') is-invalid @enderror"
                                accept="image/jpeg,image/jpg,image/png" onchange="previewFoto(event)">
                        </div>
                        <small class="text-muted d-block">Format: JPG, PNG. Maksimal: 2MB</small>
                        @error('foto_profil')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    {{-- Username --}}
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username"
                            name="username" value="{{ old('username', $user->username) }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Username untuk login ke aplikasi</small>
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                            name="password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>

                    {{-- Password Confirmation --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        <small class="form-text text-muted">Wajib diisi jika mengubah password</small>
                    </div>

                    <hr>

                    {{-- Nama --}}
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                            name="nama" value="{{ old('nama', $user->nama) }}" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email', $user->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NIP --}}
                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP</label>
                        <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip"
                            name="nip" value="{{ old('nip', $user->nip) }}">
                        @error('nip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- No HP --}}
                    <div class="mb-3">
                        <label for="no_hp" class="form-label">No. HP</label>
                        <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp"
                            name="no_hp" value="{{ old('no_hp', $user->no_hp) }}">
                        @error('no_hp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Format: 08xxxxxxxxxx</small>
                    </div>

                    {{-- Field tambahan untuk role Guru (conditional) --}}
                    <div id="guru-detail-fields"
                        style="{{ in_array(old('role', $user->role), ['guru', 'guru_piket', 'kepala_sekolah', 'kurikulum']) ? '' : 'display: none;' }}">
                        <hr class="my-3">
                        <h6 class="text-muted mb-3">Detail Profil Guru</h6>

                        {{-- Jenis Kelamin --}}
                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select class="form-control @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin"
                                name="jenis_kelamin">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L"
                                    {{ old('jenis_kelamin', $user->guru->jenis_kelamin ?? '') === 'L' ? 'selected' : '' }}>
                                    Laki-laki</option>
                                <option value="P"
                                    {{ old('jenis_kelamin', $user->guru->jenis_kelamin ?? '') === 'P' ? 'selected' : '' }}>
                                    Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div class="mb-3">
                            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                id="tanggal_lahir" name="tanggal_lahir"
                                value="{{ old('tanggal_lahir', $user->guru && $user->guru->tanggal_lahir ? $user->guru->tanggal_lahir->format('Y-m-d') : '') }}">
                            @error('tanggal_lahir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Alamat --}}
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3">{{ old('alamat', $user->guru->alamat ?? '') }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status Kepegawaian --}}
                        <div class="mb-3">
                            <label for="status_kepegawaian" class="form-label">Status Kepegawaian</label>
                            <select class="form-control @error('status_kepegawaian') is-invalid @enderror"
                                id="status_kepegawaian" name="status_kepegawaian">
                                <option value="">-- Pilih Status --</option>
                                <option value="PNS"
                                    {{ old('status_kepegawaian', $user->guru->status_kepegawaian ?? '') === 'PNS' ? 'selected' : '' }}>
                                    PNS</option>
                                <option value="PPPK"
                                    {{ old('status_kepegawaian', $user->guru->status_kepegawaian ?? '') === 'PPPK' ? 'selected' : '' }}>
                                    PPPK</option>
                                <option value="Honorer"
                                    {{ old('status_kepegawaian', $user->guru->status_kepegawaian ?? '') === 'Honorer' ? 'selected' : '' }}>
                                    Honorer</option>
                                <option value="GTY"
                                    {{ old('status_kepegawaian', $user->guru->status_kepegawaian ?? '') === 'GTY' ? 'selected' : '' }}>
                                    GTY (Guru Tetap Yayasan)</option>
                                <option value="GTT"
                                    {{ old('status_kepegawaian', $user->guru->status_kepegawaian ?? '') === 'GTT' ? 'selected' : '' }}>
                                    GTT (Guru Tidak Tetap)</option>
                            </select>
                            @error('status_kepegawaian')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    {{-- Role --}}
                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-control @error('role') is-invalid @enderror" id="role" name="role"
                            required>
                            <option value="">-- Pilih Role --</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin
                            </option>
                            <option value="guru" {{ old('role', $user->role) === 'guru' ? 'selected' : '' }}>Guru
                            </option>
                            <option value="guru_piket" {{ old('role', $user->role) === 'guru_piket' ? 'selected' : '' }}>
                                Guru Piket</option>
                            <option value="kepala_sekolah"
                                {{ old('role', $user->role) === 'kepala_sekolah' ? 'selected' : '' }}>Kepala Sekolah
                            </option>
                            <option value="kurikulum" {{ old('role', $user->role) === 'kurikulum' ? 'selected' : '' }}>
                                Kurikulum</option>
                            <option value="ketua_kelas"
                                {{ old('role', $user->role) === 'ketua_kelas' ? 'selected' : '' }}>
                                Ketua Kelas (Siswa yang generate QR)</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Guru (conditional) --}}
                    <div class="mb-3" id="guru-field"
                        style="{{ old('role', $user->role) && old('role', $user->role) !== 'guru' && old('role', $user->role) !== 'ketua_kelas' ? '' : 'display: none;' }}">
                        <label for="guru_id" class="form-label">Profil Guru <span class="text-danger">*</span></label>
                        <select class="form-control @error('guru_id') is-invalid @enderror" id="guru_id"
                            name="guru_id">
                            <option value="">-- Pilih Guru --</option>
                            @foreach ($guru_list as $guru)
                                <option value="{{ $guru->id }}"
                                    {{ old('guru_id', $user->guru_id) == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->nama }} - {{ $guru->nip ?? 'Tanpa NIP' }}
                                </option>
                            @endforeach
                        </select>
                        @error('guru_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Role Admin, Guru Piket, Kepala Sekolah, dan Kurikulum
                            memerlukan Profil Guru</small>
                    </div>

                    {{-- Kelas (conditional) --}}
                    <div class="mb-3" id="kelas-field"
                        style="{{ old('role', $user->role) === 'ketua_kelas' ? '' : 'display: none;' }}">
                        <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                        <select class="form-control @error('kelas_id') is-invalid @enderror" id="kelas_id"
                            name="kelas_id">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelas_list as $kelas)
                                <option value="{{ $kelas->id }}"
                                    {{ old('kelas_id', $user->kelas_id) == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Ketua Kelas adalah Siswa yang bertanggung jawab di kelas
                            tersebut</small>
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status"
                            required>
                            <option value="aktif" {{ old('status', $user->status) === 'aktif' ? 'selected' : '' }}>Aktif
                                (dapat login)</option>
                            <option value="nonaktif" {{ old('status', $user->status) === 'nonaktif' ? 'selected' : '' }}>
                                Nonaktif (tidak dapat login)</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update User
                        </button>
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="page-section">
                <h3 class="section-title">Informasi User</h3>
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Dibuat:</strong></td>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terakhir Update:</strong></td>
                            <td>{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if ($user->guru_id && $user->guru)
                            <tr>
                                <td><strong>Data Guru:</strong></td>
                                <td>{{ $user->guru->nama }}</td>
                            </tr>
                        @endif
                        @if ($user->kelas_id && $user->kelas)
                            <tr>
                                <td><strong>Kelas:</strong></td>
                                <td>{{ $user->kelas->nama_kelas }}</td>
                            </tr>
                        @endif
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
                                <li>Username harus unik</li>
                                <li>Password minimal 6 karakter</li>
                                <li>Kosongkan password jika tidak ingin mengubah</li>
                                <li>Role Admin, Guru Piket, Kepala Sekolah, Kurikulum memerlukan Profil Guru</li>
                                <li>Role Ketua Kelas memerlukan Kelas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for Profil Guru dropdown
            $('#guru_id').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Pilih Guru --',
                allowClear: true,
                width: '100%'
            });
        });

        // Preview foto
        function previewFoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('fotoPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        const roleSelect = document.getElementById('role');
        const guruField = document.getElementById('guru-field');
        const guruSelect = document.getElementById('guru_id');
        const guruDetailFields = document.getElementById('guru-detail-fields');
        const kelasField = document.getElementById('kelas-field');
        const kelasSelect = document.getElementById('kelas_id');

        roleSelect.addEventListener('change', function() {
            const roleValue = this.value;

            // Reset visibility
            guruField.style.display = 'none';
            guruDetailFields.style.display = 'none';
            kelasField.style.display = 'none';
            guruSelect.required = false;
            kelasSelect.required = false;

            // Show appropriate field based on role
            if (roleValue === 'ketua_kelas') {
                // Ketua Kelas = Siswa → Pilih Kelas
                kelasField.style.display = 'block';
                kelasSelect.required = true;
            } else if (roleValue === 'guru' || roleValue === 'guru_piket' || roleValue === 'kepala_sekolah' ||
                roleValue === 'kurikulum') {
                // Guru, Guru Piket, Kepala Sekolah, Kurikulum → Tampilkan field profil guru
                guruField.style.display = 'block';
                guruDetailFields.style.display = 'block';
                guruSelect.required = false;
            }
            // Admin tidak perlu guru_id atau kelas_id
        });

        // Trigger on page load if role is already selected
        if (roleSelect.value) {
            roleSelect.dispatchEvent(new Event('change'));
        }
    </script>
@endpush
