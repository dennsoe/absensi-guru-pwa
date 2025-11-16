# Routes Reference - SIAG NEKAS

Dokumentasi lengkap semua endpoint yang tersedia di aplikasi SIAG NEKAS.

---

## 🔓 Guest Routes (Belum Login)

| Method | URI      | Name       | Controller@Method            | Middleware | Keterangan           |
| ------ | -------- | ---------- | ---------------------------- | ---------- | -------------------- |
| GET    | `/`      | login      | AuthController@showLoginForm | guest      | Tampilkan form login |
| POST   | `/login` | login.post | AuthController@login         | guest      | Proses login         |

---

## 🔒 Authenticated Routes

### Authentication

| Method | URI          | Name      | Controller@Method         | Middleware | Keterangan                |
| ------ | ------------ | --------- | ------------------------- | ---------- | ------------------------- |
| POST   | `/logout`    | logout    | AuthController@logout     | auth       | Logout user               |
| GET    | `/dashboard` | dashboard | DashboardController@index | auth       | Redirect berdasarkan role |

---

## 👨‍💼 Admin Routes

**Prefix:** `/admin`  
**Middleware:** `auth`, `role:admin`, `log.activity`

| Method | URI                        | Name                | Controller@Method           | Keterangan                       |
| ------ | -------------------------- | ------------------- | --------------------------- | -------------------------------- |
| GET    | `/admin/dashboard`         | admin.dashboard     | AdminController@dashboard   | Dashboard admin dengan statistik |
| GET    | `/admin/users`             | admin.users         | AdminController@users       | List semua user                  |
| GET    | `/admin/users/create`      | admin.users.create  | AdminController@createUser  | Form tambah user                 |
| POST   | `/admin/users`             | admin.users.store   | AdminController@storeUser   | Simpan user baru                 |
| GET    | `/admin/users/{user}/edit` | admin.users.edit    | AdminController@editUser    | Form edit user                   |
| PUT    | `/admin/users/{user}`      | admin.users.update  | AdminController@updateUser  | Update user                      |
| DELETE | `/admin/users/{user}`      | admin.users.destroy | AdminController@destroyUser | Hapus user                       |

---

## 👨‍🏫 Guru Routes

**Prefix:** `/guru`  
**Middleware:** `auth`, `role:guru,ketua_kelas`

| Method | URI                       | Name                 | Controller@Method             | Keterangan                    |
| ------ | ------------------------- | -------------------- | ----------------------------- | ----------------------------- |
| GET    | `/guru/dashboard`         | guru.dashboard       | GuruController@dashboard      | Dashboard guru personal       |
| GET    | `/guru/absensi/riwayat`   | guru.absensi.riwayat | GuruController@riwayatAbsensi | Riwayat absensi dengan filter |
| GET    | `/guru/absensi/{absensi}` | guru.absensi.detail  | GuruController@detailAbsensi  | Detail 1 absensi              |

---

## 👮 Guru Piket Routes

**Prefix:** `/piket`  
**Middleware:** `auth`, `role:guru_piket`, `log.activity`

| Method | URI                     | Name                       | Controller@Method                      | Keterangan                     |
| ------ | ----------------------- | -------------------------- | -------------------------------------- | ------------------------------ |
| GET    | `/piket/dashboard`      | piket.dashboard            | GuruPiketController@dashboard          | Dashboard monitoring real-time |
| GET    | `/piket/monitoring`     | piket.monitoring           | GuruPiketController@monitoringAbsensi  | List absensi hari ini          |
| GET    | `/piket/absensi-manual` | piket.absensi-manual       | GuruPiketController@inputAbsensiManual | Form input absensi manual      |
| POST   | `/piket/absensi-manual` | piket.absensi-manual.store | GuruPiketController@storeAbsensiManual | Simpan absensi manual          |

---

## 👔 Kepala Sekolah Routes

**Prefix:** `/kepsek`  
**Middleware:** `auth`, `role:kepala_sekolah`, `log.activity`

| Method | URI                                | Name                        | Controller@Method                           | Keterangan          |
| ------ | ---------------------------------- | --------------------------- | ------------------------------------------- | ------------------- |
| GET    | `/kepsek/dashboard`                | kepsek.dashboard            | KepalaSekolahController@dashboard           | Dashboard eksekutif |
| GET    | `/kepsek/izin-cuti`                | kepsek.izin-cuti            | KepalaSekolahController@izinCuti            | List izin/cuti      |
| POST   | `/kepsek/izin-cuti/{izin}/approve` | kepsek.izin-cuti.approve    | KepalaSekolahController@approveIzin         | Approve izin        |
| POST   | `/kepsek/izin-cuti/{izin}/reject`  | kepsek.izin-cuti.reject     | KepalaSekolahController@rejectIzin          | Reject izin         |
| GET    | `/kepsek/laporan/kedisiplinan`     | kepsek.laporan.kedisiplinan | KepalaSekolahController@laporanKedisiplinan | Rekap kedisiplinan  |

---

## 📚 Kurikulum Routes

**Prefix:** `/kurikulum`  
**Middleware:** `auth`, `role:kurikulum`, `log.activity`

| Method | URI                               | Name                     | Controller@Method                 | Keterangan                              |
| ------ | --------------------------------- | ------------------------ | --------------------------------- | --------------------------------------- |
| GET    | `/kurikulum/dashboard`            | kurikulum.dashboard      | KurikulumController@dashboard     | Dashboard kurikulum                     |
| GET    | `/kurikulum/jadwal`               | kurikulum.jadwal         | KurikulumController@jadwal        | List jadwal mengajar                    |
| GET    | `/kurikulum/jadwal/create`        | kurikulum.jadwal.create  | KurikulumController@createJadwal  | Form tambah jadwal                      |
| POST   | `/kurikulum/jadwal`               | kurikulum.jadwal.store   | KurikulumController@storeJadwal   | Simpan jadwal (dengan validasi konflik) |
| GET    | `/kurikulum/jadwal/{jadwal}/edit` | kurikulum.jadwal.edit    | KurikulumController@editJadwal    | Form edit jadwal                        |
| PUT    | `/kurikulum/jadwal/{jadwal}`      | kurikulum.jadwal.update  | KurikulumController@updateJadwal  | Update jadwal                           |
| DELETE | `/kurikulum/jadwal/{jadwal}`      | kurikulum.jadwal.destroy | KurikulumController@destroyJadwal | Hapus jadwal                            |

---

## ✅ Absensi Routes

**Prefix:** `/absensi`  
**Middleware:** `auth`, `absensi.time`  
**Akses:** Semua role yang sudah login

| Method | URI                | Name                   | Controller@Method                     | Keterangan                   |
| ------ | ------------------ | ---------------------- | ------------------------------------- | ---------------------------- |
| GET    | `/absensi/scan-qr` | absensi.scan-qr        | AbsensiController@scanQr              | Halaman scan QR Code         |
| POST   | `/absensi/scan-qr` | absensi.scan-qr.proses | AbsensiController@prosesAbsensiQr     | Proses absensi QR (JSON)     |
| GET    | `/absensi/selfie`  | absensi.selfie         | AbsensiController@selfie              | Halaman selfie               |
| POST   | `/absensi/selfie`  | absensi.selfie.proses  | AbsensiController@prosesAbsensiSelfie | Proses absensi selfie (JSON) |

**Validasi CheckAbsensiTime:**

-   Hanya bisa absen Senin-Jumat
-   Tidak bisa absen di hari libur (dari tabel `libur`)
-   Return JSON 403 jika di luar waktu kerja

---

## 📅 Jadwal Routes

**Prefix:** `/jadwal`  
**Middleware:** `auth`  
**Akses:** Semua role untuk view, guru_piket untuk generate QR

| Method | URI                               | Name                  | Controller@Method                  | Middleware Tambahan | Keterangan                    |
| ------ | --------------------------------- | --------------------- | ---------------------------------- | ------------------- | ----------------------------- |
| GET    | `/jadwal/hari-ini`                | jadwal.hari-ini       | JadwalController@hariIni           | -                   | Jadwal mengajar hari ini      |
| GET    | `/jadwal/per-kelas`               | jadwal.per-kelas      | JadwalController@perKelas          | -                   | Filter jadwal by kelas        |
| GET    | `/jadwal/per-guru`                | jadwal.per-guru       | JadwalController@perGuru           | -                   | Filter jadwal by guru         |
| POST   | `/jadwal/generate-qr`             | jadwal.generate-qr    | JadwalController@generateQrCode    | role:guru_piket     | Generate QR Code untuk jadwal |
| POST   | `/jadwal/qr/{qrCode}/nonaktifkan` | jadwal.qr.nonaktifkan | JadwalController@nonaktifkanQrCode | role:guru_piket     | Nonaktifkan QR Code           |

---

## 📊 Laporan Routes

**Prefix:** `/laporan`  
**Middleware:** `auth`, `role:admin,kepala_sekolah,kurikulum`

| Method | URI                           | Name                 | Controller@Method               | Keterangan                   |
| ------ | ----------------------------- | -------------------- | ------------------------------- | ---------------------------- |
| GET    | `/laporan`                    | laporan.index        | LaporanController@index         | Rekap absensi bulanan        |
| GET    | `/laporan/export-pdf`         | laporan.export-pdf   | LaporanController@exportPdf     | Download PDF landscape A4    |
| GET    | `/laporan/export-excel`       | laporan.export-excel | LaporanController@exportExcel   | Download Excel (.xlsx)       |
| GET    | `/laporan/detail-guru/{guru}` | laporan.detail-guru  | LaporanController@detailGuru    | Detail absensi per guru      |
| POST   | `/laporan/simpan`             | laporan.simpan       | LaporanController@simpanLaporan | Simpan laporan sebagai draft |

---

## 📝 Request Parameters Reference

### Login (POST /login)

```php
[
    'username' => 'required|string',
    'password' => 'required|string',
    'remember' => 'optional|boolean',
]
```

### Store User (POST /admin/users)

```php
[
    'username' => 'required|string|unique:users,username|max:50',
    'password' => 'required|string|min:6',
    'role' => 'required|in:admin,guru,ketua_kelas,guru_piket,kepala_sekolah,kurikulum',
    'guru_id' => 'nullable|exists:guru,id',
    'status' => 'required|in:aktif,nonaktif',
]
```

### Store Jadwal (POST /kurikulum/jadwal)

```php
[
    'guru_id' => 'required|exists:guru,id',
    'kelas_id' => 'required|exists:kelas,id',
    'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
    'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat',
    'jam_mulai' => 'required|date_format:H:i',
    'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
    'tahun_ajaran' => 'required|string|max:20',
    'semester' => 'required|in:ganjil,genap',
    'ruangan' => 'nullable|string|max:50',
    'status' => 'required|in:aktif,nonaktif',
]
```

### Proses Absensi QR (POST /absensi/scan-qr)

```php
[
    'qr_code' => 'required|string',
    'latitude' => 'required|numeric',
    'longitude' => 'required|numeric',
]
```

**Response JSON:**

```json
{
    "success": true,
    "message": "Absensi berhasil dicatat.",
    "data": {
        "id": 1,
        "status_kehadiran": "hadir",
        "status_keterlambatan": "tepat_waktu",
        ...
    }
}
```

### Proses Absensi Selfie (POST /absensi/selfie)

```php
[
    'foto_selfie' => 'required|string', // base64 image
    'latitude' => 'required|numeric',
    'longitude' => 'required|numeric',
]
```

**Response JSON:**

```json
{
    "success": true,
    "message": "Absensi selfie berhasil. Menunggu validasi ketua kelas.",
    "data": {
        "id": 2,
        "foto_selfie": "absensi/selfie/selfie_1_20250116143022.jpg",
        "is_validasi_ketua_kelas": false,
        ...
    }
}
```

### Generate QR Code (POST /jadwal/generate-qr)

```php
[
    'jadwal_mengajar_id' => 'required|exists:jadwal_mengajar,id',
]
```

**Response JSON:**

```json
{
    "success": true,
    "qr_code": {
        "id": 1,
        "kode": "550e8400-e29b-41d4-a716-446655440000",
        "image": "PHN2ZyB4bWxucz0iaHR0cDovL3d...", // base64 SVG
        "expiry": "2025-01-16 14:40:00"
    }
}
```

### Reject Izin (POST /kepsek/izin-cuti/{izin}/reject)

```php
[
    'alasan_ditolak' => 'required|string|max:500',
]
```

---

## 🔐 Middleware Usage

### Role-based Protection

```php
Route::middleware(['role:admin'])->group(...); // Admin only
Route::middleware(['role:guru,ketua_kelas'])->group(...); // Multiple roles
Route::middleware(['role:admin,kepala_sekolah,kurikulum'])->group(...); // 3 roles
```

### Activity Logging

```php
Route::middleware(['log.activity'])->group(...);
// Auto log semua POST/PUT/PATCH/DELETE ke tabel log_aktivitas
```

### Time Validation

```php
Route::middleware(['absensi.time'])->group(...);
// Only allow Senin-Jumat, check holidays
```

---

## 🚀 API Endpoints (JSON Response)

Beberapa endpoint mengembalikan JSON untuk AJAX:

1. **POST /absensi/scan-qr** - Proses absensi QR
2. **POST /absensi/selfie** - Proses absensi selfie
3. **POST /jadwal/generate-qr** - Generate QR Code
4. **POST /jadwal/qr/{qrCode}/nonaktifkan** - Nonaktifkan QR

Format response standar:

```json
{
    "success": true|false,
    "message": "...",
    "data": {...} // optional
}
```

Error response (400/403/500):

```json
{
    "success": false,
    "message": "Error description"
}
```

---

## 📋 Route List Command

Untuk melihat semua route yang terdaftar:

```bash
php artisan route:list
```

Filter by name:

```bash
php artisan route:list --name=admin
php artisan route:list --name=absensi
```

Filter by method:

```bash
php artisan route:list --method=GET
php artisan route:list --method=POST
```

---

## 🔍 Named Routes Usage

Di Blade template:

```php
// Simple route
{{ route('login') }}

// Route dengan parameter
{{ route('admin.users.edit', $user->id) }}
{{ route('laporan.detail-guru', $guru->id) }}

// Route dengan query string
{{ route('laporan.index', ['bulan' => 12, 'tahun' => 2025]) }}

// Check current route
@if(request()->routeIs('admin.dashboard'))
    <!-- Active menu -->
@endif
```

Di Controller:

```php
return redirect()->route('dashboard');
return redirect()->route('admin.users')->with('success', 'User created!');
return route('laporan.export-pdf', ['bulan' => 12]);
```

---

**Dibuat:** 2025-01-16  
**Developer:** Denny Soemantri
