# CATATAN PENGEMBANGAN APLIKASI SIAG NEKAS - LARAVEL

**Tanggal Diskusi:** 16 November 2025  
**Tanggal Update Backend:** 16 Januari 2025  
**Project:** SIAG NEKAS - Sistem Informasi Absensi Guru SMK Negeri Kasomalang  
**Developer:** Denny Soemantri + User  
**Sekolah:** SMK Negeri Kasomalang, Kabupaten Subang, Jawa Barat

---

## 🎯 STATUS PENGEMBANGAN

### ✅ SELESAI (Backend - 100%)

1. **Database Schema** - 23 tabel dengan relationships lengkap
2. **Models** - 16 models dengan fillable, casts, relationships, scopes
3. **Middleware** - 3 custom middleware (CheckRole, LogActivity, CheckAbsensiTime)
4. **Controllers** - 11 controllers untuk semua role & fitur
5. **Routes** - Complete routing dengan middleware protection
6. **Authentication** - Username-based login dengan role-based redirect
7. **Business Logic** - QR validation, GPS checking, keterlambatan, approval flow

### 🔄 DALAM PROGRESS (Frontend - 0%)

1. **Views/Blade Templates** - Belum dibuat
2. **JavaScript QR Scanner** - Belum implementasi
3. **Selfie Camera Capture** - Belum implementasi
4. **GPS Geolocation** - Belum implementasi
5. **PWA Setup** - Belum implementasi

📖 **Dokumentasi Backend:** Lihat `BACKEND_IMPLEMENTATION.md` dan `ROUTES_REFERENCE.md`

---

## 📌 RINGKASAN KEPUTUSAN PENTING

### 1. TEKNOLOGI STACK

#### Backend

-   **Framework:** Laravel 12.x
-   **PHP Version:** 8.2+
-   **Database:** MySQL 8.0+
-   **Authentication:** Session-based (Laravel default)

#### Frontend

-   **CSS Framework:** Bootstrap 5.3 (Download & Install Local - BUKAN CDN)
-   **Custom CMS:** Ya, dibuat sendiri (tidak pakai template)
-   **Icons:** Bootstrap Icons
-   **Fonts:** Inter (Google Fonts)
-   **JavaScript:** Alpine.js 3.x untuk interactivity
-   **Charts:** Chart.js 4.x
-   **QR Code:** html5-qrcode library
-   **Build Tool:** Vite (Laravel default)

#### PWA

-   **Service Worker:** Custom dengan Workbox
-   **Manifest:** Dynamic dari config
-   **Offline Support:** Ya, dengan fallback page

---

## 🎨 DESIGN DECISIONS

### Bootstrap Usage

✅ **Bootstrap digunakan HANYA untuk:**

-   Grid system (container, row, col)
-   Utility classes (spacing, typography, flex)
-   Form components (form-control, form-select, dll)
-   Table components
-   Modal components
-   Alert/Toast components

❌ **Bootstrap TIDAK digunakan untuk:**

-   Navbar (custom build)
-   Sidebar (custom build)
-   Cards (custom build dengan design sendiri)
-   Buttons (custom styling)
-   Background colors/gradients (tidak ada gradient, solid white/gray only)

### Design System Principles

#### Color Scheme

```
Background: White (#ffffff) dan Light Gray (#f8fafc)
NO GRADIENT - Solid colors only
Primary: Blue (#2563eb)
Success: Green (#10b981)
Warning: Amber (#f59e0b)
Danger: Red (#ef4444)
```

#### Layout Strategy

-   **Desktop:** Sidebar + Top bar (untuk Admin, Kepala Sekolah, Kurikulum, Guru Piket)
-   **Mobile:** Top bar + Bottom navigation (untuk semua role, terutama Guru & Ketua Kelas)
-   **Responsive:** Mobile-first approach
-   **Consistency:** Desain 100% konsisten di semua halaman dan role

#### Role-Based Subtle Accents

Setiap role punya aksen warna yang sangat subtle (hanya di top bar dan active state):

-   Admin: Blue
-   Guru: Green
-   Ketua Kelas: Purple
-   Guru Piket: Orange
-   Kepala Sekolah: Indigo
-   Kurikulum: Cyan

**Tapi semua layout, component, spacing SAMA!**

---

## 📁 STRUKTUR FILE PROJECT LENGKAP

### Struktur Folder Laravel (Complete)

```
siag-nekas/
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   ├── GenerateSuratPeringatan.php
│   │   │   ├── AutoBackupDatabase.php
│   │   │   ├── CleanupExpiredQR.php
│   │   │   └── SendReminderNotification.php
│   │   └── Kernel.php
│   │
│   ├── Events/
│   │   ├── AbsensiCreated.php
│   │   ├── IzinApproved.php
│   │   ├── GuruPenggantiAssigned.php
│   │   └── NotificationSent.php
│   │
│   ├── Exceptions/
│   │   ├── AbsensiException.php
│   │   ├── QrCodeException.php
│   │   └── GpsValidationException.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── LogoutController.php
│   │   │   │
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── GuruController.php
│   │   │   │   ├── KelasController.php
│   │   │   │   ├── MataPelajaranController.php
│   │   │   │   ├── JadwalMengajarController.php
│   │   │   │   ├── GuruPiketController.php
│   │   │   │   ├── KetuaKelasController.php
│   │   │   │   ├── ApprovalController.php
│   │   │   │   ├── LaporanController.php
│   │   │   │   ├── SettingsController.php
│   │   │   │   ├── KalenderLiburController.php
│   │   │   │   ├── BackupController.php
│   │   │   │   ├── SuratPeringatanController.php
│   │   │   │   └── BroadcastController.php
│   │   │   │
│   │   │   ├── Guru/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── AbsensiController.php
│   │   │   │   ├── QrCodeController.php
│   │   │   │   ├── SelfieController.php
│   │   │   │   ├── JadwalController.php
│   │   │   │   ├── IzinController.php
│   │   │   │   ├── RiwayatAbsensiController.php
│   │   │   │   └── ProfileController.php
│   │   │   │
│   │   │   ├── KetuaKelas/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ScanQrController.php
│   │   │   │   ├── ValidasiAbsensiController.php
│   │   │   │   └── RiwayatController.php
│   │   │   │
│   │   │   ├── GuruPiket/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── MonitoringController.php
│   │   │   │   ├── AbsensiManualController.php
│   │   │   │   ├── LaporanPiketController.php
│   │   │   │   └── KontakGuruController.php
│   │   │   │
│   │   │   ├── KepalaSekolah/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── MonitoringController.php
│   │   │   │   ├── ApprovalController.php
│   │   │   │   ├── LaporanEksekutifController.php
│   │   │   │   └── AnalyticsController.php
│   │   │   │
│   │   │   ├── Kurikulum/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── JadwalMengajarController.php
│   │   │   │   ├── GuruPenggantiController.php
│   │   │   │   ├── ApprovalController.php
│   │   │   │   └── LaporanAkademikController.php
│   │   │   │
│   │   │   ├── Api/
│   │   │   │   ├── NotificationController.php
│   │   │   │   ├── AbsensiController.php
│   │   │   │   └── SettingsController.php
│   │   │   │
│   │   │   └── PwaController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── RoleMiddleware.php
│   │   │   ├── CheckActiveUser.php
│   │   │   ├── LogActivity.php
│   │   │   ├── CheckJadwalAktif.php
│   │   │   └── ValidateAbsensiTime.php
│   │   │
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   └── LoginRequest.php
│   │   │   ├── Admin/
│   │   │   │   ├── StoreGuruRequest.php
│   │   │   │   ├── UpdateGuruRequest.php
│   │   │   │   ├── StoreJadwalRequest.php
│   │   │   │   └── UpdateSettingsRequest.php
│   │   │   ├── Guru/
│   │   │   │   ├── AbsensiQrRequest.php
│   │   │   │   ├── AbsensiSelfieRequest.php
│   │   │   │   └── IzinRequest.php
│   │   │   └── GuruPiket/
│   │   │       └── AbsensiManualRequest.php
│   │   │
│   │   └── Resources/
│   │       ├── AbsensiResource.php
│   │       ├── GuruResource.php
│   │       ├── JadwalResource.php
│   │       └── NotifikasiResource.php
│   │
│   ├── Jobs/
│   │   ├── GenerateLaporanPdf.php
│   │   ├── SendEmailNotification.php
│   │   ├── SendWhatsappNotification.php
│   │   ├── GenerateSuratPeringatan.php
│   │   ├── BackupDatabase.php
│   │   ├── ProcessBulkImport.php
│   │   └── SendReminderAbsensi.php
│   │
│   ├── Listeners/
│   │   ├── SendAbsensiNotification.php
│   │   ├── LogAbsensiActivity.php
│   │   ├── UpdateRekapJamMengajar.php
│   │   └── CheckPelanggaranGuru.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Guru.php
│   │   ├── Kelas.php
│   │   ├── MataPelajaran.php
│   │   ├── JadwalMengajar.php
│   │   ├── Absensi.php
│   │   ├── QrCode.php
│   │   ├── Notifikasi.php
│   │   ├── GuruPiket.php
│   │   ├── GuruPengganti.php
│   │   ├── IzinCuti.php
│   │   ├── Pelanggaran.php
│   │   ├── PengaturanSistem.php
│   │   ├── LogAktivitas.php
│   │   ├── Libur.php
│   │   ├── Laporan.php
│   │   ├── SuratPeringatan.php
│   │   ├── BroadcastMessage.php
│   │   ├── NotifikasiPreference.php
│   │   ├── ApiKey.php
│   │   ├── BackupHistory.php
│   │   ├── RekapJamMengajar.php
│   │   └── PushSubscription.php
│   │
│   ├── Notifications/
│   │   ├── JadwalMengajarReminder.php
│   │   ├── AbsensiManualNeedApproval.php
│   │   ├── IzinNeedApproval.php
│   │   ├── GuruBelumAbsen.php
│   │   └── GuruAlphaTanpaKeterangan.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   └── RouteServiceProvider.php
│   │
│   ├── Services/
│   │   ├── AbsensiService.php
│   │   ├── QrCodeService.php
│   │   ├── GpsService.php
│   │   ├── ImageService.php
│   │   ├── NotificationService.php
│   │   ├── ApprovalService.php
│   │   ├── LaporanService.php
│   │   ├── SettingsService.php
│   │   ├── BackupService.php
│   │   ├── WhatsappService.php
│   │   ├── EmailService.php
│   │   └── SuratPeringatanService.php
│   │
│   ├── Repositories/
│   │   ├── GuruRepository.php
│   │   ├── AbsensiRepository.php
│   │   ├── JadwalRepository.php
│   │   ├── NotifikasiRepository.php
│   │   └── SettingsRepository.php
│   │
│   └── Helpers/
│       ├── DateHelper.php
│       ├── TimeHelper.php
│       ├── FormatHelper.php
│       └── ValidationHelper.php
│
├── bootstrap/
│   ├── app.php
│   └── cache/
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── auth.php
│   ├── filesystems.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   ├── absensi.php          # Custom config untuk absensi
│   ├── gps.php              # Custom config untuk GPS
│   └── pwa.php              # Custom config untuk PWA
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── GuruFactory.php
│   │   ├── KelasFactory.php
│   │   └── JadwalMengajarFactory.php
│   │
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_guru_table.php
│   │   ├── 2024_01_01_000003_create_mata_pelajaran_table.php
│   │   ├── 2024_01_01_000004_create_kelas_table.php
│   │   ├── 2024_01_01_000005_create_jadwal_mengajar_table.php
│   │   ├── 2024_01_01_000006_create_absensi_table.php
│   │   ├── 2024_01_01_000007_create_qr_codes_table.php
│   │   ├── 2024_01_01_000008_create_notifikasi_table.php
│   │   ├── 2024_01_01_000009_create_pengaturan_sistem_table.php
│   │   ├── 2024_01_01_000010_create_guru_piket_table.php
│   │   ├── 2024_01_01_000011_create_guru_pengganti_table.php
│   │   ├── 2024_01_01_000012_create_izin_cuti_table.php
│   │   ├── 2024_01_01_000013_create_pelanggaran_table.php
│   │   ├── 2024_01_01_000014_create_log_aktivitas_table.php
│   │   ├── 2024_01_01_000015_create_libur_table.php
│   │   ├── 2024_01_01_000016_create_laporan_table.php
│   │   ├── 2024_01_01_000017_create_surat_peringatan_table.php
│   │   ├── 2024_01_01_000018_create_broadcast_message_table.php
│   │   ├── 2024_01_01_000019_create_notifikasi_preferences_table.php
│   │   ├── 2024_01_01_000020_create_api_keys_table.php
│   │   ├── 2024_01_01_000021_create_backup_history_table.php
│   │   ├── 2024_01_01_000022_create_rekap_jam_mengajar_table.php
│   │   └── 2024_01_01_000023_create_push_subscriptions_table.php
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── GuruSeeder.php
│       ├── KelasSeeder.php
│       ├── MataPelajaranSeeder.php
│       ├── JadwalMengajarSeeder.php
│       ├── SettingsSeeder.php
│       └── LiburSeeder.php
│
├── public/
│   ├── index.php
│   ├── manifest.json           # PWA Manifest
│   ├── service-worker.js       # Service Worker
│   ├── offline.html           # Offline fallback page
│   ├── robots.txt
│   ├── favicon.ico
│   │
│   ├── assets/
│   │   ├── images/
│   │   │   ├── logonekas.png
│   │   │   ├── logonekas-192.png
│   │   │   ├── logonekas-512.png
│   │   │   └── placeholder-avatar.png
│   │   │
│   │   ├── css/
│   │   │   ├── app.css
│   │   │   ├── admin.css
│   │   │   ├── guru.css
│   │   │   └── mobile.css
│   │   │
│   │   ├── js/
│   │   │   ├── app.js
│   │   │   ├── qr-scanner.js
│   │   │   ├── camera.js
│   │   │   ├── gps.js
│   │   │   ├── notification.js
│   │   │   ├── chart.js
│   │   │   └── pwa-register.js
│   │   │
│   │   └── vendor/
│   │       ├── bootstrap/
│   │       │   ├── css/
│   │       │   │   ├── bootstrap.min.css
│   │       │   │   └── bootstrap.min.css.map
│   │       │   └── js/
│   │       │       ├── bootstrap.bundle.min.js
│   │       │       └── bootstrap.bundle.min.js.map
│   │       ├── bootstrap-icons/
│   │       │   └── bootstrap-icons.css
│   │       ├── alpine/
│   │       │   └── alpine.min.js
│   │       ├── chart.js/
│   │       └── html5-qrcode/
│   │
│   └── storage/
│       ├── selfies/
│       ├── documents/
│       ├── qr-codes/
│       ├── laporan/
│       ├── backup/
│       └── surat-peringatan/
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php
│   │   │   ├── admin.blade.php
│   │   │   ├── guru.blade.php
│   │   │   ├── guest.blade.php
│   │   │   └── components/
│   │   │       ├── navbar.blade.php
│   │   │       ├── sidebar.blade.php
│   │   │       ├── footer.blade.php
│   │   │       ├── bottom-nav.blade.php
│   │   │       ├── notification-badge.blade.php
│   │   │       └── breadcrumb.blade.php
│   │   │
│   │   ├── auth/
│   │   │   ├── login.blade.php
│   │   │   └── reset-password.blade.php
│   │   │
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── guru/
│   │   │   ├── kelas/
│   │   │   ├── mata-pelajaran/
│   │   │   ├── jadwal/
│   │   │   ├── guru-piket/
│   │   │   ├── ketua-kelas/
│   │   │   ├── approval/
│   │   │   ├── laporan/
│   │   │   ├── settings/
│   │   │   ├── kalender-libur/
│   │   │   ├── backup/
│   │   │   ├── surat-peringatan/
│   │   │   └── broadcast/
│   │   │
│   │   ├── guru/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── absensi/
│   │   │   ├── jadwal/
│   │   │   ├── izin/
│   │   │   ├── riwayat/
│   │   │   └── profile/
│   │   │
│   │   ├── ketua-kelas/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── scan-qr.blade.php
│   │   │   ├── validasi.blade.php
│   │   │   └── riwayat.blade.php
│   │   │
│   │   ├── guru-piket/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── monitoring.blade.php
│   │   │   ├── absensi-manual/
│   │   │   ├── laporan/
│   │   │   └── kontak-guru.blade.php
│   │   │
│   │   ├── kepala-sekolah/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── monitoring.blade.php
│   │   │   ├── approval/
│   │   │   ├── laporan/
│   │   │   └── analytics/
│   │   │
│   │   ├── kurikulum/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── jadwal/
│   │   │   ├── guru-pengganti/
│   │   │   ├── approval/
│   │   │   └── laporan/
│   │   │
│   │   ├── components/
│   │   │   ├── alert.blade.php
│   │   │   ├── modal.blade.php
│   │   │   ├── card.blade.php
│   │   │   ├── table.blade.php
│   │   │   ├── button.blade.php
│   │   │   ├── form-input.blade.php
│   │   │   ├── form-select.blade.php
│   │   │   ├── notification-item.blade.php
│   │   │   └── jadwal-card.blade.php
│   │   │
│   │   ├── pdf/
│   │   │   ├── laporan-harian.blade.php
│   │   │   ├── laporan-bulanan.blade.php
│   │   │   ├── surat-peringatan.blade.php
│   │   │   └── laporan-piket.blade.php
│   │   │
│   │   └── errors/
│   │       ├── 403.blade.php
│   │       ├── 404.blade.php
│   │       └── 500.blade.php
│   │
│   ├── lang/
│   │   └── id/
│   │       ├── auth.php
│   │       ├── validation.php
│   │       ├── pagination.php
│   │       └── messages.php
│   │
│   └── css/
│       ├── app.css                 # Main import file
│       ├── variables.css           # CSS Custom Properties
│       ├── base.css                # Reset & base styles
│       ├── layout.css              # Layout components
│       ├── components/
│       │   ├── navbar.css
│       │   ├── sidebar.css
│       │   ├── bottom-nav.css
│       │   ├── cards.css
│       │   ├── buttons.css
│       │   ├── forms.css
│       │   ├── tables.css
│       │   ├── modals.css
│       │   └── badges.css
│       ├── pages/
│       │   ├── dashboard.css
│       │   ├── auth.css
│       │   └── absensi.css
│       └── utilities.css           # Custom utilities
│
├── routes/
│   ├── web.php              # Web routes
│   ├── api.php              # API routes
│   ├── console.php          # Console commands
│   └── channels.php         # Broadcast channels
│
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   ├── selfies/
│   │   │   ├── documents/
│   │   │   ├── qr-codes/
│   │   │   └── avatars/
│   │   ├── backups/
│   │   └── exports/
│   │
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   ├── testing/
│   │   └── views/
│   │
│   └── logs/
│       ├── laravel.log
│       └── absensi.log
│
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   ├── Admin/
│   │   ├── Guru/
│   │   └── Api/
│   │
│   ├── Unit/
│   │   ├── Services/
│   │   └── Models/
│   │
│   ├── TestCase.php
│   └── CreatesApplication.php
│
├── .env                     # Environment configuration
├── .env.example            # Environment template
├── .gitignore
├── artisan                 # Laravel CLI
├── composer.json           # PHP dependencies
├── composer.lock
├── package.json            # NPM dependencies
├── package-lock.json
├── phpunit.xml            # PHPUnit configuration
├── README.md
└── vite.config.js         # Vite configuration
```

---

## 🎯 DEVELOPMENT APPROACH

### Phase 1: Foundation ✅

-   [x] Laravel project created
-   [x] Database schema designed (migrations created)
-   [x] Models with Eloquent relations created
-   [ ] Install Bootstrap locally
-   [ ] Setup Vite configuration
-   [ ] Create custom CSS files

### Phase 2: Authentication & Layout

-   [ ] Multi-role login system
-   [ ] Custom navbar component
-   [ ] Custom sidebar component
-   [ ] Bottom navigation component
-   [ ] Master layouts (admin, mobile, auth)
-   [ ] Middleware for role-based access

### Phase 3: Core Features

-   [ ] Dashboard per role
-   [ ] CRUD Master Data (Guru, Kelas, Mapel, Jadwal)
-   [ ] QR Code generation & scanning
-   [ ] Selfie capture & validation
-   [ ] GPS validation
-   [ ] Absensi workflow

### Phase 4: Advanced Features

-   [ ] Approval workflow (Izin, Cuti, Absensi Manual)
-   [ ] Notification system (real-time polling)
-   [ ] Laporan & Export (Excel, PDF)
-   [ ] Settings management
-   [ ] Surat Peringatan otomatis

### Phase 5: PWA & Polish

-   [ ] Service Worker implementation
-   [ ] Manifest.json dynamic
-   [ ] Push notification
-   [ ] Offline support
-   [ ] Performance optimization
-   [ ] UI/UX refinement

---

## 💡 KEY REQUIREMENTS

### Responsiveness

-   **Primary:** Mobile-first (Guru menggunakan HP untuk absensi)
-   **Secondary:** Desktop admin panel yang user-friendly
-   Semua component harus 100% responsive

### Consistency

-   Tidak boleh ada perbedaan desain antar halaman
-   Component reusable dengan Blade components
-   Design system yang ketat (variables.css)

### Performance

-   Fast loading (< 3 detik)
-   Optimized images (selfie auto-compress)
-   Efficient database queries
-   Minimal JavaScript bundle

### Security

-   CSRF protection
-   XSS prevention
-   SQL injection prevention (Eloquent ORM)
-   File upload validation
-   GPS spoofing detection (haversine formula)
-   QR code signature validation

---

## 📊 BUSINESS LOGIC SUMMARY

### Absensi Workflow

#### QR Code Method

1. Guru generate QR (expired 5 menit, one-time use)
2. Ketua Kelas scan QR dengan camera HP
3. System validasi: token, expired, GPS (optional)
4. Ketua Kelas input PIN/signature
5. Absensi tersimpan dengan status (hadir/terlambat)

#### Selfie Method

1. Guru buka camera untuk selfie
2. Ambil foto + capture GPS location
3. System validasi: GPS radius, image size
4. Auto-compress image (800x600px, 75% quality)
5. Absensi tersimpan

### Toleransi Waktu

-   Bisa absen **30 menit sebelum** jam mengajar
-   Masih bisa absen **60 menit setelah** jam selesai
-   Toleransi terlambat: **15 menit** (configurable)
-   Status "terlambat" jika absen > 15 menit dari jam mulai

### GPS Validation

-   Sekolah latitude & longitude disimpan di settings
-   Radius: 200 meter (configurable)
-   Calculation: Haversine formula
-   Mode: Strict (wajib) atau Loose (optional)

### Approval Workflow

```
Izin/Sakit/Cuti → Guru submit → Admin/Kepsek/Kurikulum approve/reject
Absensi Manual → Guru Piket input → Admin approve/reject
Guru Pengganti → Kurikulum assign → Guru B terima → Kurikulum confirm
```

### Notification System

-   **Polling AJAX:** Setiap 30 detik
-   **15 menit sebelum mengajar:** Notif ke Guru
-   **10 menit belum absen:** Notif ke Guru Piket
-   **30 menit belum absen:** Notif ke Admin
-   **Multi-channel:** Web (default), Email (optional), WhatsApp (optional)

### Surat Peringatan Otomatis

-   **SP1:** 3x alfa dalam 30 hari
-   **SP2:** 5x alfa dalam 30 hari
-   **SP3:** 7x alfa dalam 30 hari
-   Auto-generate PDF dengan template
-   Email notification ke guru & kepala sekolah

---

## 🗂️ DATABASE STRUCTURE

### Main Tables (23 tables total)

1. users - Multi-role authentication
2. guru - Data guru lengkap
3. kelas - Data kelas
4. mata_pelajaran - Data mapel
5. jadwal_mengajar - Jadwal per guru per hari
6. absensi - Record absensi (QR/Selfie)
7. qr_codes - QR code temporary storage
8. notifikasi - Notification queue
9. guru_piket - Assignment guru piket per hari
10. guru_pengganti - Assignment guru pengganti
11. izin_cuti - Pengajuan izin/sakit/cuti
12. pelanggaran - Record pelanggaran (alfa, terlambat)
13. pengaturan_sistem - Settings key-value ⭐ **DIPERLUAS**
14. log_aktivitas - Audit log semua action
15. libur - Kalender hari libur
16. laporan - Generated reports
17. surat_peringatan - SP1/SP2/SP3 records
18. broadcast_message - Broadcast message
19. notifikasi_preferences - User notif preferences
20. api_keys - External API keys (WA, Email)
21. backup_history - Backup records
22. rekap_jam_mengajar - Monthly teaching hours
23. push_subscriptions - PWA push subscription

### Key Relationships

-   User → Guru (1:1)
-   Guru → JadwalMengajar (1:N)
-   JadwalMengajar → Absensi (1:N)
-   Guru → QrCode (1:N)
-   User → Notifikasi (1:N)

### Settings Categories Structure

Tabel `pengaturan_sistem` akan dikelompokkan dalam kategori berikut:

#### 1. **Branding & Appearance**

```
- app_name (Nama Aplikasi: SIAG NEKAS)
- app_tagline (Tagline/Slogan: Sistem Informasi Absensi Guru SMK Negeri Kasomalang)
- app_version (Versi Aplikasi - auto dari env)
- app_logo (Path logo aplikasi: /assets/images/logonekas.png)
- app_logo_sekolah (Path logo sekolah: /assets/images/logonekas.png)
- app_favicon (Path favicon: /assets/images/logonekas.png)
- app_footer_text (Text footer: © 2025 SMK Negeri Kasomalang)
- app_watermark (Watermark untuk PDF: logonekas.png)
- theme_primary_color (Warna tema utama)
```

#### 2. **Sekolah Information**

```
- sekolah_nama (Nama Sekolah: SMK Negeri Kasomalang)
- sekolah_npsn (NPSN: 20219345)
- sekolah_alamat (Alamat Lengkap: Jl. Raya Kasomalang, Kasomalang Kulon, Kec. Kasomalang, Kabupaten Subang, Jawa Barat 41281)
- sekolah_email (Email: info@smknkasomalang.sch.id)
- sekolah_telepon (Telepon/Fax: (0260) 520xxx)
- sekolah_website (Website URL: https://smknkasomalang.sch.id)
- sekolah_kepala_nama (Nama Kepala Sekolah: [Sesuaikan dengan data aktual])
- sekolah_kepala_nip (NIP Kepala Sekolah: [Sesuaikan dengan data aktual])
```

#### 3. **PWA Configuration**

```
- pwa_name (PWA Name: SIAG NEKAS)
- pwa_short_name (Short Name: SIAG NEKAS)
- pwa_description (Description: Sistem Informasi Absensi Guru SMK Negeri Kasomalang dengan QR Code dan Selfie)
- pwa_theme_color (Theme Color)
- pwa_background_color (Background Color)
- pwa_display (Display Mode: standalone/fullscreen)
- pwa_orientation (Orientation: portrait/landscape)
```

#### 4. **System Settings**

```
- system_timezone (Timezone)
- system_date_format (Date Format)
- system_time_format (Time Format)
- system_language (Language: id/en)
- system_maintenance_mode (Maintenance Mode: true/false)
- system_maintenance_message (Maintenance Message)
```

#### 5. **Upload & Storage**

```
- upload_max_size_mb (Max File Size in MB)
- upload_allowed_types (Allowed File Types: jpg,png,pdf)
- storage_location (Location: local/s3/gcs)
- storage_cleanup_enabled (Auto Cleanup: true/false)
- storage_retention_days (Retention Days)
```

#### 6. **Security Settings**

```
- security_session_timeout (Session Timeout Minutes)
- security_force_password_change (Force Change Days)
- security_password_min_length (Min Password Length)
- security_password_require_uppercase (Require Uppercase)
- security_password_require_number (Require Number)
- security_password_require_special (Require Special Char)
- security_max_login_attempts (Max Login Attempts)
- security_lockout_duration (Lockout Duration Minutes)
- security_2fa_enabled (2FA Enabled)
- security_ip_whitelist_enabled (IP Whitelist Enabled)
- security_ip_whitelist (IP Addresses comma-separated)
```

#### 7. **Academic Settings**

```
- academic_tahun_ajaran (Tahun Ajaran Aktif)
- academic_semester (Semester: Ganjil/Genap)
- academic_semester_start (Tanggal Mulai)
- academic_semester_end (Tanggal Selesai)
- academic_hari_kerja (Hari Kerja comma-separated)
- academic_jam_kerja_mulai (Jam Mulai)
- academic_jam_kerja_selesai (Jam Selesai)
- academic_durasi_jam_pelajaran (Durasi Jam Pelajaran Menit)
```

#### 8. **Integration Settings**

```
- integration_siakad_enabled (SIAKAD Sync Enabled)
- integration_siakad_url (SIAKAD API URL)
- integration_siakad_key (SIAKAD API Key - encrypted)
- integration_google_maps_key (Google Maps API Key)
- integration_firebase_key (Firebase FCM Key - encrypted)
```

#### 9. **Legal & Compliance**

```
- legal_privacy_policy_url (Privacy Policy URL)
- legal_terms_url (Terms of Service URL)
- legal_data_retention_years (Data Retention Years)
```

#### 10. **GPS Settings** (sudah ada di skema original)

```
- gps_enabled
- gps_latitude
- gps_longitude
- gps_radius
- gps_strict_mode
```

#### 11. **QR Code Settings** (sudah ada)

```
- qr_expiry_minutes
- qr_auto_refresh
- qr_size
```

#### 12. **Absensi Settings** (sudah ada)

```
- absensi_qr_enabled
- absensi_selfie_enabled
- toleransi_terlambat
- absen_sebelum
- absen_setelah
- wajib_selfie
- wajib_gps
- wajib_validasi_ketua
```

#### 13. **Notification Settings** (sudah ada)

```
- notif_web_enabled
- notif_email_enabled
- notif_whatsapp_enabled
- notif_sebelum_jadwal_menit
- notif_ajax_polling_interval
```

#### 14. **Email Settings** (sudah ada)

```
- email_enabled
- email_smtp_host
- email_smtp_port
- email_smtp_secure
- email_username
- email_password (encrypted)
- email_from_address
- email_from_name
```

#### 15. **WhatsApp Settings** (sudah ada)

```
- whatsapp_enabled
- whatsapp_provider
- whatsapp_api_key (encrypted)
- whatsapp_api_url
- whatsapp_sender_number
```

#### 16. **Surat Peringatan Settings** (sudah ada)

```
- sp_enabled
- sp1_threshold
- sp2_threshold
- sp3_threshold
- sp_periode_hari
- sp_auto_generate
```

#### 17. **Backup Settings** (sudah ada)

```
- backup_auto_enabled
- backup_time
- backup_frequency
- backup_retention_days
- backup_location
- backup_email_notification
```

---

## 🔧 CONFIGURATION FILES

### .env Key Variables

```env
# Bootstrap (Local - not CDN)
VITE_BOOTSTRAP_PATH=/assets/vendor/bootstrap

# Design
APP_THEME_COLOR=#2563eb
APP_BACKGROUND=white
APP_NO_GRADIENT=true

# GPS
GPS_ENABLED=true
GPS_LATITUDE=-6.200000
GPS_LONGITUDE=106.816666
GPS_RADIUS=200

# QR
QR_EXPIRY_MINUTES=5
QR_AUTO_REFRESH=true

# Absensi
TOLERANSI_TERLAMBAT=15
ABSEN_SEBELUM=30
ABSEN_SETELAH=60

# Surat Peringatan
SP1_THRESHOLD=3
SP2_THRESHOLD=5
SP3_THRESHOLD=7

# Branding & Appearance
APP_NAME="SIAG NEKAS"
APP_TAGLINE="Sistem Informasi Absensi Guru SMK Negeri Kasomalang"
APP_VERSION=1.0.0
APP_LOGO=/assets/images/logonekas.png
APP_LOGO_SEKOLAH=/assets/images/logonekas.png
APP_FAVICON=/assets/images/logonekas.png
APP_FOOTER_TEXT="© 2025 SMK Negeri Kasomalang. All rights reserved."
APP_WATERMARK=/assets/images/logonekas.png

# Sekolah Info
SEKOLAH_NAMA="SMK Negeri Kasomalang"
SEKOLAH_NPSN=20219345
SEKOLAH_ALAMAT="Jl. Raya Kasomalang, Kasomalang Kulon, Kec. Kasomalang, Kabupaten Subang, Jawa Barat 41281"
SEKOLAH_EMAIL=info@smknkasomalang.sch.id
SEKOLAH_TELEPON=(0260) 520xxx
SEKOLAH_WEBSITE=https://smknkasomalang.sch.id
SEKOLAH_KEPALA_SEKOLAH="[Nama Kepala Sekolah]"
SEKOLAH_KEPALA_SEKOLAH_NIP=[NIP Kepala Sekolah]

# PWA Settings
PWA_NAME="SIAG NEKAS"
PWA_SHORT_NAME="SIAG NEKAS"
PWA_DESCRIPTION="Sistem Informasi Absensi Guru SMK Negeri Kasomalang dengan QR Code dan Selfie"
PWA_THEME_COLOR=#2563eb
PWA_BACKGROUND_COLOR=#ffffff
PWA_DISPLAY=standalone
PWA_ORIENTATION=portrait

# System Settings
TIMEZONE=Asia/Jakarta
DATE_FORMAT="d-m-Y"
TIME_FORMAT="H:i"
LANGUAGE=id
MAINTENANCE_MODE=false
MAINTENANCE_MESSAGE="Sistem sedang dalam perbaikan. Mohon kembali lagi nanti."

# Upload & Storage
MAX_FILE_UPLOAD_MB=5
ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf
STORAGE_LOCATION=local
AUTO_CLEANUP_OLD_FILES=true
FILE_RETENTION_DAYS=365

# Security
SESSION_TIMEOUT_MINUTES=120
FORCE_PASSWORD_CHANGE_DAYS=90
PASSWORD_MIN_LENGTH=8
PASSWORD_REQUIRE_UPPERCASE=true
PASSWORD_REQUIRE_NUMBER=true
PASSWORD_REQUIRE_SPECIAL=false
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION_MINUTES=15
TWO_FACTOR_AUTH_ENABLED=false
IP_WHITELIST_ENABLED=false
IP_WHITELIST_ADDRESSES=

# Academic Settings
TAHUN_AJARAN_AKTIF=2024/2025
SEMESTER_AKTIF=Ganjil
SEMESTER_START_DATE=2024-07-01
SEMESTER_END_DATE=2024-12-31
HARI_KERJA=Senin,Selasa,Rabu,Kamis,Jumat
JAM_KERJA_MULAI=07:00
JAM_KERJA_SELESAI=16:00
DURASI_JAM_PELAJARAN_MENIT=45

# Integration (Optional)
SIAKAD_SYNC_ENABLED=false
SIAKAD_API_URL=
SIAKAD_API_KEY=
GOOGLE_MAPS_API_KEY=
FIREBASE_FCM_SERVER_KEY=

# Legal
PRIVACY_POLICY_URL=/privacy-policy
TERMS_OF_SERVICE_URL=/terms-of-service
DATA_RETENTION_YEARS=5
```

---

## 📦 DEPENDENCIES & PACKAGES

### PHP Dependencies (composer.json)

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "guzzlehttp/guzzle": "^7.8",
        "simplesoftwareio/simple-qrcode": "^4.2",
        "intervention/image": "^3.0",
        "barryvdh/laravel-dompdf": "^2.0",
        "maatwebsite/laravel-excel": "^3.1",
        "spatie/laravel-permission": "^6.0",
        "spatie/laravel-activitylog": "^4.0"
    },
    "require-dev": {
        "laravel/pint": "^1.0",
        "laravel/sail": "^1.26",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.0",
        "phpunit/phpunit": "^11.0",
        "fakerphp/faker": "^1.23"
    }
}
```

### NPM Dependencies (package.json)

```json
{
    "devDependencies": {
        "@vitejs/plugin-vue": "^5.0.0",
        "autoprefixer": "^10.4.17",
        "axios": "^1.6.4",
        "laravel-vite-plugin": "^1.0",
        "postcss": "^8.4.33",
        "vite": "^5.0"
    },
    "dependencies": {
        "alpinejs": "^3.13.5",
        "bootstrap": "^5.3.2",
        "bootstrap-icons": "^1.11.3",
        "chart.js": "^4.4.1",
        "html5-qrcode": "^2.3.8"
    }
}
```

---

## ⚙️ CUSTOM CONFIGURATION FILES

### config/absensi.php

```php
<?php

return [
    // Metode Absensi
    'metode' => [
        'qr_code' => env('ABSENSI_QR_ENABLED', true),
        'selfie' => env('ABSENSI_SELFIE_ENABLED', true),
    ],

    // QR Code Settings
    'qr' => [
        'expiry_minutes' => env('QR_EXPIRY_MINUTES', 5),
        'auto_refresh' => env('QR_AUTO_REFRESH', true),
        'size' => env('QR_SIZE', 300),
    ],

    // Toleransi Waktu
    'toleransi' => [
        'terlambat_menit' => env('TOLERANSI_TERLAMBAT', 15),
        'absen_sebelum_menit' => env('ABSEN_SEBELUM', 30),
        'absen_setelah_menit' => env('ABSEN_SETELAH', 60),
    ],

    // Validasi
    'validasi' => [
        'wajib_selfie' => env('WAJIB_SELFIE', true),
        'wajib_gps' => env('WAJIB_GPS', true),
        'wajib_validasi_ketua' => env('WAJIB_VALIDASI_KETUA', true),
    ],

    // Selfie Settings
    'selfie' => [
        'max_size_mb' => env('SELFIE_MAX_SIZE', 5),
        'compression_quality' => env('SELFIE_QUALITY', 75),
        'resize_width' => env('SELFIE_WIDTH', 800),
        'resize_height' => env('SELFIE_HEIGHT', 600),
    ],

    // Surat Peringatan
    'surat_peringatan' => [
        'enabled' => env('SP_ENABLED', true),
        'sp1_threshold' => env('SP1_THRESHOLD', 3),
        'sp2_threshold' => env('SP2_THRESHOLD', 5),
        'sp3_threshold' => env('SP3_THRESHOLD', 7),
        'periode_hari' => env('SP_PERIODE', 30),
        'auto_generate' => env('SP_AUTO_GENERATE', true),
    ],
];
```

### config/gps.php

```php
<?php

return [
    'enabled' => env('GPS_ENABLED', true),

    'sekolah' => [
        'latitude' => env('GPS_LATITUDE', '-6.4167'),
        'longitude' => env('GPS_LONGITUDE', '107.7667'),
        'radius_meter' => env('GPS_RADIUS', 100),
    ],

    'strict_mode' => env('GPS_STRICT_MODE', false),
    'show_map' => env('GPS_SHOW_MAP', true),
];
```

### config/pwa.php

```php
<?php

return [
    'name' => env('PWA_NAME', 'SIAG NEKAS'),
    'short_name' => env('PWA_SHORT_NAME', 'SIAG NEKAS'),
    'description' => 'Sistem Informasi Absensi Guru SMK Negeri Kasomalang dengan QR Code dan Selfie',
    'theme_color' => env('PWA_THEME_COLOR', '#2563eb'),
    'background_color' => env('PWA_BG_COLOR', '#ffffff'),
    'display' => 'standalone',
    'orientation' => 'portrait',
    'start_url' => '/',
    'scope' => '/',

    'icons' => [
        [
            'src' => '/assets/images/logonekas-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
        ],
        [
            'src' => '/assets/images/logonekas-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
        ],
    ],

    'offline' => [
        'enabled' => true,
        'fallback_url' => '/offline',
        'cache_strategy' => 'network_first',
    ],
];
```

---

## 🔐 MIDDLEWARE & ROUTING

### Middleware Configuration

```php
// app/Http/Kernel.php (atau bootstrap/app.php di Laravel 11)
protected $middlewareAliases = [
    'role.admin' => \App\Http\Middleware\RoleMiddleware::class.':admin',
    'role.guru' => \App\Http\Middleware\RoleMiddleware::class.':guru',
    'role.ketua_kelas' => \App\Http\Middleware\RoleMiddleware::class.':ketua_kelas',
    'role.guru_piket' => \App\Http\Middleware\RoleMiddleware::class.':guru_piket',
    'role.kepala_sekolah' => \App\Http\Middleware\RoleMiddleware::class.':kepala_sekolah',
    'role.kurikulum' => \App\Http\Middleware\RoleMiddleware::class.':kurikulum',
    'active.user' => \App\Http\Middleware\CheckActiveUser::class,
    'log.activity' => \App\Http\Middleware\LogActivity::class,
    'check.jadwal' => \App\Http\Middleware\CheckJadwalAktif::class,
    'validate.absensi.time' => \App\Http\Middleware\ValidateAbsensiTime::class,
];
```

### Route Groups Structure

```php
// routes/web.php - Simplified Structure

// Guest routes (login, reset password)
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Authenticated routes
Route::middleware(['auth', 'active.user', 'log.activity'])->group(function () {

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('role.admin')->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('guru', Admin\GuruController::class);
        Route::resource('jadwal', Admin\JadwalMengajarController::class);
        // ... dst
    });

    // Guru routes
    Route::prefix('guru')->name('guru.')->middleware('role.guru')->group(function () {
        Route::get('/dashboard', [Guru\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/absensi/qr', [Guru\QrCodeController::class, 'generate'])->name('absensi.qr');
        Route::post('/absensi/selfie', [Guru\SelfieController::class, 'store'])->name('absensi.selfie');
        // ... dst
    });

    // Ketua Kelas routes
    Route::prefix('ketua-kelas')->name('ketua-kelas.')->middleware('role.ketua_kelas')->group(function () {
        Route::get('/dashboard', [KetuaKelas\DashboardController::class, 'index'])->name('dashboard');
        Route::post('/scan-qr', [KetuaKelas\ScanQrController::class, 'validate'])->name('scan-qr');
        // ... dst
    });

    // Guru Piket routes
    Route::prefix('guru-piket')->name('guru-piket.')->middleware('role.guru_piket')->group(function () {
        Route::get('/dashboard', [GuruPiket\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/monitoring', [GuruPiket\MonitoringController::class, 'index'])->name('monitoring');
        // ... dst
    });

    // Kepala Sekolah routes
    Route::prefix('kepala-sekolah')->name('kepala-sekolah.')->middleware('role.kepala_sekolah')->group(function () {
        Route::get('/dashboard', [KepalaSekolah\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics', [KepalaSekolah\AnalyticsController::class, 'index'])->name('analytics');
        // ... dst
    });

    // Kurikulum routes
    Route::prefix('kurikulum')->name('kurikulum.')->middleware('role.kurikulum')->group(function () {
        Route::get('/dashboard', [Kurikulum\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('jadwal', Kurikulum\JadwalMengajarController::class);
        // ... dst
    });

    // Common routes (all authenticated users)
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi.index');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// API routes (for AJAX calls)
Route::prefix('api')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/notifications', [Api\NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [Api\NotificationController::class, 'markAsRead']);
    Route::get('/settings/{key}', [Api\SettingsController::class, 'get']);
});

// PWA routes
Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/service-worker.js', [PwaController::class, 'serviceWorker'])->name('pwa.sw');
Route::get('/offline', function () {
    return view('offline');
})->name('offline');
```

---

## 📝 COMPONENT ARCHITECTURE

### Blade Components (Reusable)

```
resources/views/components/
├── layout/
│   ├── navbar.blade.php          # Custom navbar (bukan Bootstrap)
│   ├── sidebar.blade.php         # Custom sidebar (bukan Bootstrap)
│   ├── bottom-nav.blade.php      # Custom bottom nav (mobile)
│   └── footer.blade.php
├── ui/
│   ├── card.blade.php            # Custom card (bukan Bootstrap card)
│   ├── stats-card.blade.php
│   ├── jadwal-card.blade.php
│   ├── button.blade.php          # Custom button styling
│   ├── badge.blade.php
│   ├── avatar.blade.php
│   ├── empty-state.blade.php
│   └── loading.blade.php
├── form/
│   ├── input.blade.php           # Bootstrap form-control wrapper
│   ├── select.blade.php
│   ├── textarea.blade.php
│   └── file-upload.blade.php
└── feature/
    ├── qr-scanner.blade.php
    ├── camera-capture.blade.php
    ├── gps-indicator.blade.php
    └── notification-badge.blade.php
```

---

## 🎨 CSS ARCHITECTURE

### CSS Custom Properties (variables.css)

```css
:root {
    /* Colors - NO GRADIENT */
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --color-primary: #2563eb;
    --color-success: #10b981;
    --color-warning: #f59e0b;
    --color-danger: #ef4444;

    /* Spacing */
    --spacing-xs: 0.5rem;
    --spacing-sm: 0.75rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;

    /* Border Radius */
    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;

    /* Typography */
    --font-family: "Inter", sans-serif;
    --font-size-sm: 0.875rem;
    --font-size-base: 1rem;
    --font-size-lg: 1.125rem;

    /* Shadows */
    --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);

    /* Transitions */
    --transition-base: 200ms ease;
}
```

### Settings Admin Interface

**URL:** `/admin/pengaturan`

**Layout:**

```
┌─────────────────────────────────────┐
│  Pengaturan Sistem                  │
├─────────────────────────────────────┤
│  [Tab: Branding]                    │
│  [Tab: Sekolah]                     │
│  [Tab: PWA]                         │
│  [Tab: Sistem]                      │
│  [Tab: Keamanan]                    │
│  [Tab: Akademik]                    │
│  [Tab: Absensi]                     │
│  [Tab: Notifikasi]                  │
│  [Tab: Integrasi]                   │
├─────────────────────────────────────┤
│  Form Input Settings:               │
│  • Text Input (nama aplikasi)       │
│  • File Upload (logo, favicon)      │
│  • Color Picker (theme color)       │
│  • Toggle Switch (enabled/disabled) │
│  • Number Input (timeout, durasi)   │
│  • Time Picker (jam kerja)          │
│  • Select Dropdown (timezone, etc)  │
│                                     │
│  [Reset ke Default] [Simpan]        │
└─────────────────────────────────────┘
```

**Validation Rules per Setting Type:**

```php
'app_name' => 'required|string|max:100',
'app_version' => 'required|regex:/^\d+\.\d+\.\d+$/',
'app_logo' => 'nullable|image|mimes:png,jpg|max:2048',
'app_favicon' => 'nullable|image|mimes:ico,png|max:512',
'theme_primary_color' => 'required|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
'sekolah_email' => 'required|email',
'sekolah_npsn' => 'required|digits:8',
'gps_latitude' => 'required|numeric|between:-90,90',
'gps_longitude' => 'required|numeric|between:-180,180',
'gps_radius' => 'required|integer|min:10|max:1000',
'security_session_timeout' => 'required|integer|min:5|max:1440',
'security_password_min_length' => 'required|integer|min:6|max:32',
```

**Settings Helper Service:**

```php
// app/Services/SettingsService.php
class SettingsService {
    public function get(string $key, $default = null);
    public function set(string $key, $value): bool;
    public function setMany(array $settings): bool;
    public function getByCategory(string $category): Collection;
    public function getAllCategories(): array;
    public function resetToDefault(string $key): bool;
    public function resetCategoryToDefault(string $category): bool;
}
```

**Access Settings di Blade:**

```blade
<!-- Menggunakan helper -->
{{ settings('app_name') }}
{{ settings('app_version', '1.0.0') }}

<!-- Menggunakan config (cached) -->
{{ config('app_settings.app_name') }}

<!-- Logo -->
<img src="{{ asset(settings('app_logo')) }}" alt="Logo">

<!-- Footer -->
<footer>{{ settings('app_footer_text') }}</footer>
```

**Settings Cache Strategy:**

```php
// Cache all settings untuk performa
Cache::remember('app.settings', 3600, function () {
    return PengaturanSistem::pluck('value', 'key')->toArray();
});

// Invalidate cache saat update
event(new SettingsUpdated($key, $value));
```

### Naming Convention

````css
/* BEM-like naming */
.app-card {
}
.app-card__header {
}
.app-card__body {
}
.app-card--primary {
}

.sidebar {
}
.sidebar__menu {
}
.sidebar__item {
}
.sidebar__item--active {
}

.bottom-nav {
}
.bottom-nav__item {
}
.bottom-nav__item--active {
}
```    }
}
````

---

## 🚀 DEPLOYMENT NOTES

### Server Requirements

-   PHP 8.2+
-   MySQL 8.0+
-   Composer 2.x
-   Node.js 18+ (for build process)
-   GD/Imagick extension (for image processing)

### Hosting Options

1. **Shared Hosting** (Budget)
    - cPanel with SSH access
    - PHP 8.2 support
    - MySQL remote access
2. **VPS** (Recommended)
    - Ubuntu 22.04 LTS
    - LEMP stack
    - SSL certificate (Let's Encrypt)
3. **Cloud** (Scalable)
    - AWS EC2 / DigitalOcean Droplet
    - Load balancer (optional)
    - RDS for database

### Build Process

```bash
# Development
npm run dev

# Production
npm run build
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ✅ QUALITY CHECKLIST

### Before Implementation

-   [ ] Semua requirements clear
-   [ ] Design system approved
-   [ ] Database schema final
-   [ ] Color palette confirmed

### During Development

-   [ ] Code follows PSR-12
-   [ ] All routes have middleware
-   [ ] All inputs validated
-   [ ] All queries optimized
-   [ ] All images compressed
-   [ ] All sensitive data encrypted

### Before Deployment

-   [ ] All features tested (manual)
-   [ ] Mobile responsive verified
-   [ ] PWA installable
-   [ ] Performance optimized
-   [ ] Security audit passed
-   [ ] Backup system working
-   [ ] Documentation complete

---

## 📞 STAKEHOLDER NOTES

### User Feedback Priority

1. **Guru (Primary User):**

    - Proses absensi harus cepat (< 30 detik)
    - UI simple, tidak banyak klik
    - Notifikasi jelas dan tepat waktu

2. **Admin:**

    - Dashboard informatif
    - Approval workflow jelas
    - Export laporan mudah

3. **Kepala Sekolah:**
    - Analytics meaningful
    - Executive summary readable
    - Decision support data

### Success Metrics

-   95%+ guru sukses absen mandiri (tanpa bantuan)
-   < 5% error rate (QR scan, GPS validation)
-   < 3 detik loading time (homepage)
-   100% mobile responsive
-   0 critical security issues

---

## 🔄 CHANGE LOG

**16 November 2025:**

-   Initial discussion & requirement gathering
-   Technology stack decided
-   Design system created
-   Laravel structure planned
-   Custom CMS approach confirmed
-   Bootstrap local installation confirmed (NOT CDN)
-   No gradient backgrounds (solid white/gray only)
-   Consistency requirement emphasized

---

## 📌 NEXT STEPS

1. ✅ **Setup Laravel Project** (DONE)
2. ⏳ **Install Bootstrap Locally** (NEXT)
3. ⏳ **Create Custom CSS Architecture**
4. ⏳ **Build Layout Components**
5. ⏳ **Implement Authentication**
6. ⏳ **Build Dashboard per Role**
7. ⏳ **Implement Core Features**
8. ⏳ **Testing & Refinement**
9. ⏳ **PWA Implementation**
10. ⏳ **Deployment**

---

## 💬 IMPORTANT QUOTES FROM DISCUSSION

> "Saya ingin aplikasi ini sangat responsive untuk mobile, tetapi untuk admin saya ingin gunakan admin panel template."

> "Saya ingin semuanya sama desainnya. Jangan ada desain di setiap halaman yang berbeda. Dalam artian desain yang konsisten."

> "Bootstrap terbaru tetapi tidak ingin gunakan bootstrap CDN, melainkan gunakan bootstrap yang didownload dan dipasang di aplikasi."

> "Bootstrap digunakan untuk konten saja, bukan untuk desain seperti navbar, sidebar dan yang lainnya."

> "Saya tidak ingin ada background body gradient. Cukup warna putih yang disesuaikan saja."

---

**File ini adalah acuan utama untuk development. Update setiap ada perubahan requirement atau design decision.**
