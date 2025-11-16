@extends('layouts.app')

@section('title', 'Edit Kelas')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Kelas</h1>
            <p class="page-subtitle">Ubah data kelas {{ $kela->nama_kelas }}</p>
        </div>
        <a href="{{ route('admin.kelas.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="page-section">
                <form action="{{ route('admin.kelas.update', $kela->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Nama Kelas --}}
                    <div class="mb-3">
                        <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror" id="nama_kelas"
                            name="nama_kelas" value="{{ old('nama_kelas', $kela->nama_kelas) }}" required
                            placeholder="Contoh: X-IPA-1">
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
                            <option value="10" {{ old('tingkat', $kela->tingkat) == '10' ? 'selected' : '' }}>Kelas 10
                            </option>
                            <option value="11" {{ old('tingkat', $kela->tingkat) == '11' ? 'selected' : '' }}>Kelas 11
                            </option>
                            <option value="12" {{ old('tingkat', $kela->tingkat) == '12' ? 'selected' : '' }}>Kelas 12
                            </option>
                        </select>
                        @error('tingkat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Jurusan --}}
                    <div class="mb-3">
                        <label for="jurusan" class="form-label">Jurusan</label>
                        <input type="text" class="form-control @error('jurusan') is-invalid @enderror" id="jurusan"
                            name="jurusan" value="{{ old('jurusan', $kela->jurusan) }}"
                            placeholder="Contoh: IPA, IPS, atau kosongkan">
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
                            <option value="2024/2025"
                                {{ old('tahun_ajaran', $kela->tahun_ajaran) == '2024/2025' ? 'selected' : '' }}>2024/2025
                            </option>
                            <option value="2025/2026"
                                {{ old('tahun_ajaran', $kela->tahun_ajaran) == '2025/2026' ? 'selected' : '' }}>2025/2026
                            </option>
                            <option value="2026/2027"
                                {{ old('tahun_ajaran', $kela->tahun_ajaran) == '2026/2027' ? 'selected' : '' }}>2026/2027
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
                                    {{ old('wali_kelas_id', $kela->wali_kelas_id) == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->nama }} - {{ $guru->nip ?? 'Tanpa NIP' }}
                                </option>
                            @endforeach
                        </select>
                        @error('wali_kelas_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Opsional, dapat dikosongkan</small>
                    </div>

                    {{-- Ketua Kelas --}}
                    <div class="mb-3">
                        <label for="ketua_kelas_user_id" class="form-label">Ketua Kelas (Siswa)</label>
                        <select class="form-control @error('ketua_kelas_user_id') is-invalid @enderror"
                            id="ketua_kelas_user_id" name="ketua_kelas_user_id">
                            <option value="">-- Pilih Ketua Kelas --</option>
                            @foreach ($ketua_kelas_list as $siswa)
                                <option value="{{ $siswa->id }}"
                                    {{ old('ketua_kelas_user_id', $kela->ketua_kelas_user_id) == $siswa->id ? 'selected' : '' }}>
                                    {{ $siswa->nama }} - {{ $siswa->username }}
                                </option>
                            @endforeach
                        </select>
                        @error('ketua_kelas_user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Hanya menampilkan siswa yang belum ditugaskan atau siswa dari
                            kelas ini</small>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Kelas
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
                <h3 class="section-title">Informasi Kelas</h3>
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>Dibuat:</strong></td>
                            <td>{{ $kela->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terakhir Update:</strong></td>
                            <td>{{ $kela->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Jadwal:</strong></td>
                            <td>{{ $kela->jadwalMengajar->count() }} mata pelajaran</td>
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
                                <li>Nama kelas harus unik</li>
                                <li>Ubah ketua kelas akan mereset assignment lama</li>
                                <li>Pastikan data wali kelas sudah benar</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
