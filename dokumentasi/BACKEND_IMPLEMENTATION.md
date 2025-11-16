# Backend Implementation - SIAG NEKAS

## 📊 Status Implementasi Backend

✅ **SELESAI** - Semua komponen backend sudah diimplementasi

---

## 🔐 Authentication & Middleware

### Middleware yang Telah Dibuat

1. **CheckRole** (`app/Http/Middleware/CheckRole.php`)
    - Role-based access control
    - Support multiple roles dengan variadic parameter
    - Validasi status user (aktif/nonaktif)
    - Return 403 jika unauthorized
2. **LogActivity** (`app/Http/Middleware/LogActivity.php`)

    - Audit trail untuk semua operasi POST/PUT/PATCH/DELETE
    - Logging IP address dan user agent
    - Automatic table detection dari route name
    - Silent fail dengan logger untuk menghindari blocking

3. **CheckAbsensiTime** (`app/Http/Middleware/CheckAbsensiTime.php`)
    - Validasi waktu absensi (Senin-Jumat only)
    - Check holiday dari tabel `libur`
    - Check weekend (Sabtu & Minggu)
    - Return JSON 403 jika di luar jam kerja

### Middleware Registration

File: `bootstrap/app.php`

```php
$middleware->alias([
    'role' => \App\Http\Middleware\CheckRole::class,
    'log.activity' => \App\Http\Middleware\LogActivity::class,
    'absensi.time' => \App\Http\Middleware\CheckAbsensiTime::class,
]);
```

---

## 🎮 Controllers

### 1. AuthController (`app/Http/Controllers/Auth/AuthController.php`)

**Fungsi:**

-   Login dengan username (bukan email)
-   Validasi status user (aktif/nonaktif)
-   Update last_login timestamp
-   Logout dengan session regeneration

**Methods:**

-   `showLoginForm()` - Tampilkan form login
-   `login(Request)` - Proses login
-   `logout(Request)` - Proses logout

---

### 2. DashboardController (`app/Http/Controllers/DashboardController.php`)

**Fungsi:**

-   Redirect ke dashboard sesuai role

**Role Mapping:**

-   `admin` → `admin.dashboard`
-   `guru` → `guru.dashboard`
-   `guru_piket` → `piket.dashboard`
-   `kepala_sekolah` → `kepsek.dashboard`
-   `kurikulum` → `kurikulum.dashboard`
-   `ketua_kelas` → `guru.dashboard`

---

### 3. AdminController (`app/Http/Controllers/Admin/AdminController.php`)

**Fungsi:**

-   Dashboard dengan statistik guru & absensi
-   User management (CRUD)

**Methods:**

-   `dashboard()` - Statistik hari ini
-   `users()` - List semua user
-   `createUser()` - Form tambah user
-   `storeUser(Request)` - Simpan user baru
-   `editUser(User)` - Form edit user
-   `updateUser(Request, User)` - Update user
-   `destroyUser(User)` - Hapus user (dengan proteksi admin terakhir)

**Statistik Dashboard:**

-   Total guru, kelas, jadwal aktif
-   Guru hadir/terlambat/izin hari ini

---

### 4. GuruController (`app/Http/Controllers/Guru/GuruController.php`)

**Fungsi:**

-   Dashboard personal guru
-   Riwayat absensi
-   Detail absensi

**Methods:**

-   `dashboard()` - Jadwal hari ini & statistik bulan ini
-   `riwayatAbsensi(Request)` - History dengan filter bulan/tahun
-   `detailAbsensi(Absensi)` - Detail 1 absensi dengan authorization

**Statistik Dashboard:**

-   Jadwal mengajar hari ini
-   Total hadir/terlambat/izin bulan ini

---

### 5. GuruPiketController (`app/Http/Controllers/GuruPiket/GuruPiketController.php`)

**Fungsi:**

-   Monitoring real-time absensi
-   Input absensi manual untuk guru yang tidak bisa scan QR

**Methods:**

-   `dashboard()` - Monitoring guru hadir/belum absen/terlambat/izin
-   `monitoringAbsensi()` - Real-time list absensi hari ini
-   `inputAbsensiManual()` - Form input manual
-   `storeAbsensiManual(Request)` - Simpan absensi manual

**Fitur Khusus:**

-   Detect guru yang belum absen
-   Input manual dengan metode_absensi = 'manual'
-   Auto set dibuat_oleh ke guru piket yang login

---

### 6. KepalaSekolahController (`app/Http/Controllers/KepalaSekolah/KepalaSekolahController.php`)

**Fungsi:**

-   Dashboard eksekutif dengan overview lengkap
-   Approval izin/cuti
-   Laporan kedisiplinan

**Methods:**

-   `dashboard()` - Statistik kehadiran bulan ini & izin pending
-   `izinCuti()` - List semua izin/cuti
-   `approveIzin(IzinCuti)` - Approve izin
-   `rejectIzin(Request, IzinCuti)` - Reject izin dengan alasan
-   `laporanKedisiplinan(Request)` - Rekap per guru

**Dashboard Features:**

-   Statistik hadir/izin/alpha/terlambat
-   List izin yang perlu approval
-   Pelanggaran bulan ini
-   Guru yang sering terlambat (>= 3x)

---

### 7. KurikulumController (`app/Http/Controllers/Kurikulum/KurikulumController.php`)

**Fungsi:**

-   Manajemen jadwal mengajar
-   Deteksi konflik jadwal

**Methods:**

-   `dashboard()` - Statistik jadwal & deteksi konflik
-   `jadwal()` - List semua jadwal
-   `createJadwal()` - Form tambah jadwal
-   `storeJadwal(Request)` - Simpan dengan validasi konflik
-   `editJadwal(JadwalMengajar)` - Form edit
-   `updateJadwal(Request, JadwalMengajar)` - Update jadwal
-   `destroyJadwal(JadwalMengajar)` - Hapus jadwal

**Validasi Konflik:**

-   Cek guru di waktu & hari yang sama
-   Prevent double booking

---

### 8. AbsensiController (`app/Http/Controllers/Absensi/AbsensiController.php`)

**Fungsi:**

-   Absensi via QR Code
-   Absensi via Selfie + GPS

**Methods:**

-   `scanQr()` - Halaman scan QR
-   `prosesAbsensiQr(Request)` - Validasi QR + GPS + hitung keterlambatan
-   `selfie()` - Halaman selfie
-   `prosesAbsensiSelfie(Request)` - Validasi GPS + simpan foto + tandai perlu validasi ketua kelas
-   `validateGPS($lat, $lng)` - Haversine formula untuk cek radius
-   `saveSelfie($base64, $guruId)` - Save & resize image

**Validasi QR:**

-   QR Code status aktif
-   Belum kadaluarsa
-   GPS dalam radius
-   Belum absen hari ini
-   Ada jadwal mengajar

**Validasi Selfie:**

-   GPS dalam radius
-   Foto disimpan di `storage/app/public/absensi/selfie/`
-   Resize ke 800px width
-   Set `is_validasi_ketua_kelas = false` (menunggu approval)

**Toleransi Keterlambatan:**

-   Default 15 menit (dari config)
-   Hitung menit terlambat otomatis

---

### 9. JadwalController (`app/Http/Controllers/Jadwal/JadwalController.php`)

**Fungsi:**

-   View jadwal (hari ini, per kelas, per guru)
-   Generate QR Code untuk jadwal (Guru Piket only)

**Methods:**

-   `hariIni()` - Jadwal hari ini semua guru
-   `perKelas(Request)` - Filter by kelas
-   `perGuru(Request)` - Filter by guru
-   `generateQrCode(Request)` - Generate UUID QR dengan expiry 10 menit
-   `nonaktifkanQrCode(QrCode)` - Matikan QR manual

**QR Code Generation:**

-   UUID unique string
-   Expiry 10 menit (configurable)
-   Format SVG base64
-   Size 300x300px

---

### 10. LaporanController (`app/Http/Controllers/Laporan/LaporanController.php`)

**Fungsi:**

-   Rekap absensi bulanan
-   Export PDF & Excel
-   Detail per guru

**Methods:**

-   `index(Request)` - Rekap dengan filter bulan/tahun/guru
-   `exportPdf(Request)` - Generate PDF landscape A4
-   `exportExcel(Request)` - Generate XLSX via Maatwebsite Excel
-   `detailGuru(Request, Guru)` - Detail absensi per guru
-   `simpanLaporan(Request)` - Simpan sebagai arsip draft

**Data Rekap:**

-   Total absensi
-   Hadir, Izin, Sakit, Alpha, Dinas Luar
-   Terlambat & total menit terlambat
-   Persentase kehadiran

---

### 11. AbsensiExport (`app/Exports/AbsensiExport.php`)

**Interface:**

-   `FromCollection` - Query data
-   `WithHeadings` - Header kolom
-   `WithMapping` - Format data per row
-   `WithStyles` - Bold header

**Kolom Excel:**

1. No
2. NIP
3. Nama Guru
4. Total Absensi
5. Hadir
6. Izin
7. Sakit
8. Alpha
9. Dinas Luar
10. Terlambat
11. Total Menit Terlambat
12. Persentase Kehadiran

---

## 🛣️ Routes Configuration

File: `routes/web.php`

### Route Groups

1. **Guest Routes** (Middleware: `guest`)

    - `/` → Login form
    - `POST /login` → Process login

2. **Authenticated Routes** (Middleware: `auth`)

    - `/dashboard` → Role-based redirect
    - `POST /logout` → Logout

3. **Admin Routes** (Middleware: `auth`, `role:admin`, `log.activity`)

    - Prefix: `/admin`
    - User management CRUD

4. **Guru Routes** (Middleware: `auth`, `role:guru,ketua_kelas`)

    - Prefix: `/guru`
    - Dashboard & riwayat absensi

5. **Guru Piket Routes** (Middleware: `auth`, `role:guru_piket`, `log.activity`)

    - Prefix: `/piket`
    - Monitoring & input manual

6. **Kepala Sekolah Routes** (Middleware: `auth`, `role:kepala_sekolah`, `log.activity`)

    - Prefix: `/kepsek`
    - Approval izin & laporan kedisiplinan

7. **Kurikulum Routes** (Middleware: `auth`, `role:kurikulum`, `log.activity`)

    - Prefix: `/kurikulum`
    - Jadwal mengajar CRUD

8. **Absensi Routes** (Middleware: `auth`, `absensi.time`)

    - Prefix: `/absensi`
    - QR scan & selfie (semua role bisa akses)

9. **Jadwal Routes** (Middleware: `auth`)

    - Prefix: `/jadwal`
    - View jadwal (semua role)
    - Generate QR (guru_piket only)

10. **Laporan Routes** (Middleware: `auth`, `role:admin,kepala_sekolah,kurikulum`)
    - Prefix: `/laporan`
    - Rekap, export PDF/Excel

---

## 📦 Dependencies

### Installed Packages

1. **simplesoftwareio/simple-qrcode** (v4.2)
    - QR Code generation
    - Format: SVG, PNG
2. **intervention/image** (v3.11)
    - Image processing
    - Resize selfie photos
3. **barryvdh/laravel-dompdf** (v3.1)
    - PDF generation
    - Laporan export
4. **maatwebsite/excel** (v3.1)
    - Excel export
    - Custom headings & mapping

---

## 🔄 Business Logic Highlights

### Absensi Workflow

1. **Guru melakukan absensi:**

    - Scan QR Code ATAU Upload Selfie
    - GPS validation (radius 100m dari sekolah)
    - Cek sudah absen hari ini atau belum
    - Cek jadwal mengajar hari ini

2. **Jika QR Code:**

    - Validasi QR aktif & belum expired
    - Hitung keterlambatan dari jam_mulai jadwal
    - Auto approve (langsung masuk)

3. **Jika Selfie:**

    - Simpan foto ke storage
    - Tandai `is_validasi_ketua_kelas = false`
    - Menunggu approval ketua kelas

4. **Guru Piket:**
    - Bisa input absensi manual untuk guru yang bermasalah
    - Set metode_absensi = 'manual'

### Approval Flow (Izin/Cuti)

1. Guru submit izin/cuti → status `pending`
2. Kepala Sekolah approve/reject
3. Jika approve → status `disetujui`, isi `disetujui_oleh` & `tanggal_disetujui`
4. Jika reject → status `ditolak`, isi alasan di `keterangan`

### Laporan Generation

1. **Rekap Bulanan:**

    - Query aggregate dari tabel absensi
    - Group by guru_id
    - Hitung total hadir, izin, sakit, alpha, terlambat

2. **Export PDF:**

    - Template Blade: `laporan.pdf`
    - Landscape A4
    - Include tanggal cetak

3. **Export Excel:**
    - Class `AbsensiExport`
    - Header + data + styling
    - Persentase kehadiran otomatis

---

## 🚀 Next Steps (Frontend)

Saat ini **backend sudah 100% selesai**. Yang masih perlu dibuat:

1. ✅ **Views (Blade Templates)**

    - Layout master dengan sidebar
    - Login page
    - Dashboard untuk setiap role
    - Form CRUD
    - Tabel data

2. ✅ **JavaScript Interactions**

    - QR Code scanner (HTML5 Camera API)
    - Selfie capture (getUserMedia)
    - GPS geolocation
    - AJAX untuk real-time monitoring

3. ✅ **CSS/Bootstrap Styling**

    - Bootstrap 5.3 local (sudah terinstall)
    - Alpine.js untuk interaktivitas

4. ✅ **PWA Implementation**
    - Service Worker
    - Push Notifications
    - Offline capability

---

## 📝 Testing Checklist

### Unit Testing Required:

-   [ ] Middleware: CheckRole dengan berbagai role
-   [ ] Middleware: CheckAbsensiTime di weekend & holiday
-   [ ] AbsensiController: GPS validation (dalam & luar radius)
-   [ ] AbsensiController: QR expiry validation
-   [ ] JadwalController: Konflik jadwal detection
-   [ ] LaporanController: Rekap calculation accuracy

### Integration Testing Required:

-   [ ] Login flow → redirect ke dashboard yang benar
-   [ ] Absensi QR → insert ke database → tampil di monitoring
-   [ ] Absensi Selfie → upload foto → tampil preview
-   [ ] Approval izin → update status → notifikasi guru
-   [ ] Export PDF → download file → validate content
-   [ ] Export Excel → download file → validate columns

---

## 🔒 Security Features

1. **Authentication:**

    - Username-based (bukan email)
    - Password hashed dengan bcrypt
    - Session management dengan regeneration

2. **Authorization:**

    - Role-based access control (6 roles)
    - Status checking (aktif/nonaktif)
    - Route middleware protection

3. **Audit Trail:**

    - Log semua aktivitas POST/PUT/PATCH/DELETE
    - IP address & user agent tracking
    - Table name & record ID detection

4. **Data Validation:**

    - Form request validation
    - GPS radius validation (Haversine formula)
    - QR code expiry check
    - Jadwal konflik prevention

5. **File Security:**
    - Selfie photos stored in `storage/app/public`
    - Image resize to 800px (prevent large uploads)
    - JPEG compression 80%

---

## 📊 Database Relations Summary

Sudah diimplementasi di **16 Models**:

-   User → Guru (hasOne)
-   Guru → JadwalMengajar (hasMany)
-   JadwalMengajar → Absensi (hasMany)
-   Absensi → QrCode (belongsTo)
-   IzinCuti → Guru (belongsTo)
-   Pelanggaran → Guru (belongsTo)
-   LogAktivitas → User (belongsTo)

Semua relationship sudah di-define dengan proper foreign keys dan eager loading.

---

## ✅ Completion Status

| Component   | Status  | Files                 |
| ----------- | ------- | --------------------- |
| Middleware  | ✅ 100% | 3 files               |
| Controllers | ✅ 100% | 11 files              |
| Routes      | ✅ 100% | 1 file                |
| Exports     | ✅ 100% | 1 file                |
| Models      | ✅ 100% | 16 files (sebelumnya) |
| Migrations  | ✅ 100% | 23 files (sebelumnya) |
| Config      | ✅ 100% | 5 files (sebelumnya)  |

**Total Backend Progress: 100%** ✅

---

**Dibuat:** 2025-01-16  
**Last Update:** 2025-01-16  
**Developer:** Denny Soemantri + User
