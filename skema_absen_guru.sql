-- ==========================================
-- DATABASE: SISTEM ABSENSI GURU v3.5
-- Platform: MySQL 5.7+
-- Charset: utf8mb4
-- ==========================================

SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;

-- ==========================================
-- 1. TABEL USERS
-- ==========================================
CREATE TABLE `users` (
    `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM(
        'admin',
        'guru',
        'ketua_kelas',
        'guru_piket',
        'kepala_sekolah',
        'kurikulum'
    ) NOT NULL,
    `status` ENUM(
        'aktif',
        'nonaktif',
        'suspended'
    ) DEFAULT 'aktif',
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    INDEX `idx_username` (`username`),
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 2. TABEL GURU
-- ==========================================
CREATE TABLE `guru` (
    `guru_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `nip` VARCHAR(20) UNIQUE,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100),
    `no_hp` VARCHAR(15),
    `alamat` TEXT,
    `foto` VARCHAR(255),
    `jenis_kelamin` ENUM('L', 'P') NOT NULL,
    `tanggal_lahir` DATE,
    `status_kepegawaian` ENUM(
        'PNS',
        'PPPK',
        'Honorer',
        'GTT',
        'GTY'
    ) DEFAULT 'PNS',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`guru_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    INDEX `idx_nip` (`nip`),
    INDEX `idx_nama` (`nama`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 3. TABEL MATA PELAJARAN
-- ==========================================
CREATE TABLE `mata_pelajaran` (
    `mapel_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `kode_mapel` VARCHAR(10) NOT NULL UNIQUE,
    `nama_mapel` VARCHAR(100) NOT NULL,
    `deskripsi` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`mapel_id`),
    INDEX `idx_kode` (`kode_mapel`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 4. TABEL KELAS
-- ==========================================
CREATE TABLE `kelas` (
    `kelas_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_kelas` VARCHAR(50) NOT NULL,
    `tingkat` ENUM('10', '11', '12') NOT NULL,
    `jurusan` VARCHAR(50),
    `wali_kelas_id` INT UNSIGNED,
    `ketua_kelas_user_id` INT UNSIGNED,
    `tahun_ajaran` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`kelas_id`),
    FOREIGN KEY (`wali_kelas_id`) REFERENCES `guru` (`guru_id`) ON DELETE SET NULL,
    FOREIGN KEY (`ketua_kelas_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    INDEX `idx_nama_kelas` (`nama_kelas`),
    INDEX `idx_tahun_ajaran` (`tahun_ajaran`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 5. TABEL JADWAL MENGAJAR
-- ==========================================
CREATE TABLE `jadwal_mengajar` (
    `jadwal_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `guru_id` INT UNSIGNED NOT NULL,
    `kelas_id` INT UNSIGNED NOT NULL,
    `mapel_id` INT UNSIGNED NOT NULL,
    `hari` ENUM(
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu'
    ) NOT NULL,
    `jam_mulai` TIME NOT NULL,
    `jam_selesai` TIME NOT NULL,
    `ruangan` VARCHAR(50),
    `tahun_ajaran` VARCHAR(20) NOT NULL,
    `semester` ENUM('Ganjil', 'Genap') NOT NULL,
    `status` ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`jadwal_id`),
    FOREIGN KEY (`guru_id`) REFERENCES `guru` (`guru_id`) ON DELETE CASCADE,
    FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`kelas_id`) ON DELETE CASCADE,
    FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`mapel_id`) ON DELETE CASCADE,
    INDEX `idx_guru_hari` (`guru_id`, `hari`),
    INDEX `idx_kelas_hari` (`kelas_id`, `hari`),
    INDEX `idx_tahun_semester` (`tahun_ajaran`, `semester`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 6. TABEL ABSENSI
-- ==========================================
CREATE TABLE `absensi` (
    `absensi_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `jadwal_id` INT UNSIGNED NOT NULL,
    `guru_id` INT UNSIGNED NOT NULL,
    `tanggal` DATE NOT NULL,
    `jam_masuk` TIME,
    `jam_keluar` TIME,
    `status_kehadiran` ENUM(
        'hadir',
        'terlambat',
        'izin',
        'sakit',
        'alpha',
        'dinas',
        'cuti'
    ) DEFAULT 'alpha',
    `metode_absensi` ENUM('qr_code', 'selfie', 'manual') NOT NULL,
    `foto_selfie` VARCHAR(255),
    `qr_code_data` VARCHAR(255),
    `latitude` DECIMAL(10, 8),
    `longitude` DECIMAL(11, 8),
    `validasi_gps` BOOLEAN DEFAULT FALSE,
    `jarak_dari_sekolah` INT,
    `keterangan` TEXT,
    `file_pendukung` VARCHAR(255),
    `validasi_ketua_kelas` BOOLEAN DEFAULT FALSE,
    `ketua_kelas_user_id` INT UNSIGNED,
    `waktu_validasi_ketua` DATETIME,
    `created_by` INT UNSIGNED,
    `approved_by` INT UNSIGNED,
    `approved_at` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`absensi_id`),
    FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_mengajar` (`jadwal_id`) ON DELETE CASCADE,
    FOREIGN KEY (`guru_id`) REFERENCES `guru` (`guru_id`) ON DELETE CASCADE,
    FOREIGN KEY (`ketua_kelas_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    INDEX `idx_guru_tanggal` (`guru_id`, `tanggal`),
    INDEX `idx_jadwal_tanggal` (`jadwal_id`, `tanggal`),
    INDEX `idx_status` (`status_kehadiran`),
    INDEX `idx_tanggal` (`tanggal`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 7. TABEL QR CODE
-- ==========================================
CREATE TABLE `qr_codes` (
    `qr_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `guru_id` INT UNSIGNED NOT NULL,
    `jadwal_id` INT UNSIGNED NOT NULL,
    `qr_data` VARCHAR(255) NOT NULL UNIQUE,
    `qr_image_path` VARCHAR(255),
    `expired_at` DATETIME NOT NULL,
    `is_used` BOOLEAN DEFAULT FALSE,
    `used_at` DATETIME,
    `used_by_ketua_kelas` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`qr_id`),
    FOREIGN KEY (`guru_id`) REFERENCES `guru` (`guru_id`) ON DELETE CASCADE,
    FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_mengajar` (`jadwal_id`) ON DELETE CASCADE,
    FOREIGN KEY (`used_by_ketua_kelas`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    INDEX `idx_qr_data` (`qr_data`),
    INDEX `idx_expired` (`expired_at`),
    INDEX `idx_guru_jadwal` (`guru_id`, `jadwal_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 8. TABEL NOTIFIKASI
-- ==========================================
CREATE TABLE `notifikasi` (
    `notifikasi_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `judul` VARCHAR(255) NOT NULL,
    `pesan` TEXT NOT NULL,
    `tipe` ENUM(
        'info',
        'warning',
        'success',
        'danger'
    ) DEFAULT 'info',
    `kategori` ENUM(
        'jadwal',
        'absensi',
        'peringatan',
        'sistem',
        'pengumuman'
    ) NOT NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `read_at` DATETIME,
    `link_url` VARCHAR(255),
    `icon` VARCHAR(50),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`notifikasi_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    INDEX `idx_user_read` (`user_id`, `is_read`),
    INDEX `idx_created` (`created_at`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 9. TABEL GURU PIKET
-- ==========================================
CREATE TABLE `guru_piket` (
    `piket_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `guru_id` INT UNSIGNED NOT NULL,
    `hari` ENUM(
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu'
    ) NOT NULL,
    `tahun_ajaran` VARCHAR(20) NOT NULL,
    `status` ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`piket_id`),
    FOREIGN KEY (`guru_id`) REFERENCES `guru` (`guru_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_guru_hari` (
        `guru_id`,
        `hari`,
        `tahun_ajaran`
    ),
    INDEX `idx_hari` (`hari`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 10. TABEL GURU PENGGANTI
-- ==========================================
CREATE TABLE `guru_pengganti` (
    `pengganti_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `jadwal_id` INT UNSIGNED NOT NULL,
    `guru_asli_id` INT UNSIGNED NOT NULL,
    `guru_pengganti_id` INT UNSIGNED NOT NULL,
    `tanggal` DATE NOT NULL,
    `alasan` TEXT NOT NULL,
    `status` ENUM(
        'pending',
        'approved',
        'rejected'
    ) DEFAULT 'pending',
    `approved_by` INT UNSIGNED,
    `approved_at` DATETIME,
    `catatan_admin` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`pengganti_id`),
    FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_mengajar` (`jadwal_id`) ON DELETE CASCADE,
    FOREIGN KEY (`guru_asli_id`) REFERENCES `guru` (`guru_id`) ON DELETE CASCADE,
    FOREIGN KEY (`guru_pengganti_id`) REFERENCES `guru` (`guru_id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    INDEX `idx_tanggal` (`tanggal`),
    INDEX `idx_status` (`status`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 11. TABEL PENGATURAN SISTEM
-- ==========================================
CREATE TABLE `pengaturan_sistem` (
    `setting_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `kategori` VARCHAR(50) NOT NULL,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT NOT NULL,
    `tipe_data` ENUM(
        'string',
        'number',
        'boolean',
        'json',
        'array'
    ) DEFAULT 'string',
    `deskripsi` TEXT,
    `is_public` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`setting_id`),
    INDEX `idx_kategori` (`kategori`),
    INDEX `idx_key` (`key`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 12. TABEL LOG AKTIVITAS
-- ==========================================
CREATE TABLE `log_aktivitas` (
    `log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED,
    `aksi` VARCHAR(100) NOT NULL,
    `tabel` VARCHAR(50),
    `record_id` INT UNSIGNED,
    `data_lama` TEXT,
    `data_baru` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    INDEX `idx_user_aksi` (`user_id`, `aksi`),
    INDEX `idx_created` (`created_at`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 13. TABEL LIBUR & HARI PENTING
-- ==========================================
CREATE TABLE `libur` (
    `libur_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nama_libur` VARCHAR(100) NOT NULL,
    `tanggal_mulai` DATE NOT NULL,
    `tanggal_selesai` DATE NOT NULL,
    `jenis` ENUM(
        'nasional',
        'sekolah',
        'semester',
        'ujian'
    ) NOT NULL,
    `deskripsi` TEXT,
    `tahun_ajaran` VARCHAR(20),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`libur_id`),
    INDEX `idx_tanggal` (
        `tanggal_mulai`,
        `tanggal_selesai`
    )
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 14. TABEL LAPORAN
-- ==========================================
CREATE TABLE `laporan` (
    `laporan_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `judul` VARCHAR(255) NOT NULL,
    `tipe_laporan` ENUM(
        'harian',
        'mingguan',
        'bulanan',
        'semester',
        'tahunan',
        'custom'
    ) NOT NULL,
    `periode_mulai` DATE NOT NULL,
    `periode_selesai` DATE NOT NULL,
    `file_path` VARCHAR(255),
    `format` ENUM('pdf', 'excel', 'csv') DEFAULT 'pdf',
    `dibuat_oleh` INT UNSIGNED,
    `data_json` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`laporan_id`),
    FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    INDEX `idx_tipe_periode` (
        `tipe_laporan`,
        `periode_mulai`
    )
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 15. TABEL IZIN & CUTI
-- ==========================================
CREATE TABLE `izin_cuti` (
    `izin_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `guru_id` INT UNSIGNED NOT NULL,
    `jenis` ENUM(
        'izin',
        'sakit',
        'cuti',
        'dinas',
        'lainnya'
    ) NOT NULL,
    `tanggal_mulai` DATE NOT NULL,
    `tanggal_selesai` DATE NOT NULL,
    `alasan` TEXT NOT NULL,
    `file_pendukung` VARCHAR(255),
    `status` ENUM(
        'pending',
        'approved',
        'rejected'
    ) DEFAULT 'pending',
    `approved_by` INT UNSIGNED,
    `approved_at` DATETIME,
    `catatan_admin` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`izin_id`),
    FOREIGN KEY (`guru_id`) REFERENCES `guru` (`guru_id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    INDEX `idx_guru_tanggal` (`guru_id`, `tanggal_mulai`),
    INDEX `idx_status` (`status`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 16. TABEL PELANGGARAN
-- ==========================================
CREATE TABLE `pelanggaran` (
    `pelanggaran_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `guru_id` INT UNSIGNED NOT NULL,
    `jenis_pelanggaran` ENUM(
        'alpha',
        'terlambat',
        'tidak_absen_keluar',
        'tidak_sesuai_jadwal'
    ) NOT NULL,
    `tanggal` DATE NOT NULL,
    `jadwal_id` INT UNSIGNED,
    `keterangan` TEXT,
    `sanksi` TEXT,
    `poin` INT DEFAULT 0,
    `status` ENUM(
        'open',
        'follow_up',
        'resolved'
    ) DEFAULT 'open',
    `ditangani_oleh` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`pelanggaran_id`),
    FOREIGN KEY (`guru_id`) REFERENCES `guru` (`guru_id`) ON DELETE CASCADE,
    FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_mengajar` (`jadwal_id`) ON DELETE SET NULL,
    FOREIGN KEY (`ditangani_oleh`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
    INDEX `idx_guru_tanggal` (`guru_id`, `tanggal`),
    INDEX `idx_status` (`status`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- 17. TABEL PUSH SUBSCRIPTION (PWA)
-- ==========================================
CREATE TABLE `push_subscriptions` (
    `subscription_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `endpoint` TEXT NOT NULL,
    `auth` VARCHAR(255) NOT NULL,
    `p256dh` VARCHAR(255) NOT NULL,
    `user_agent` VARCHAR(255),
    `is_active` BOOLEAN DEFAULT TRUE,
    `last_used` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`subscription_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    INDEX `idx_user_active` (`user_id`, `is_active`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ==========================================
-- DATA AWAL: ADMIN DEFAULT
-- ==========================================
INSERT INTO
    `users` (
        `username`,
        `password`,
        `role`,
        `status`
    )
VALUES (
        'admin',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin',
        'aktif'
    );
-- Password: password (silakan ganti setelah login pertama)

-- ==========================================
-- DATA AWAL: PENGATURAN SISTEM
-- ==========================================
INSERT INTO
    `pengaturan_sistem` (
        `kategori`,
        `key`,
        `value`,
        `tipe_data`,
        `deskripsi`,
        `is_public`
    )
VALUES
    -- Umum
    (
        'umum',
        'nama_sekolah',
        'SMA Negeri 1',
        'string',
        'Nama Sekolah',
        TRUE
    ),
    (
        'umum',
        'alamat_sekolah',
        'Jl. Contoh No. 123, Jakarta',
        'string',
        'Alamat Sekolah',
        TRUE
    ),
    (
        'umum',
        'email_sekolah',
        'info@smansa.sch.id',
        'string',
        'Email Sekolah',
        TRUE
    ),
    (
        'umum',
        'telepon_sekolah',
        '021-1234567',
        'string',
        'Telepon Sekolah',
        TRUE
    ),
    (
        'umum',
        'logo_sekolah',
        '/assets/images/logo.png',
        'string',
        'Path Logo Sekolah',
        TRUE
    ),

-- GPS & Lokasi
(
    'gps',
    'gps_latitude',
    '-6.200000',
    'string',
    'Latitude Sekolah',
    FALSE
),
(
    'gps',
    'gps_longitude',
    '106.816666',
    'string',
    'Longitude Sekolah',
    FALSE
),
(
    'gps',
    'gps_radius',
    '200',
    'number',
    'Radius GPS (meter)',
    FALSE
),
(
    'gps',
    'gps_required',
    'true',
    'boolean',
    'Validasi GPS Wajib',
    FALSE
),

-- QR Code
(
    'qr',
    'qr_expiry_time',
    '300',
    'number',
    'Masa Berlaku QR (detik)',
    FALSE
),
(
    'qr',
    'qr_auto_refresh',
    'true',
    'boolean',
    'Auto Refresh QR',
    FALSE
),
(
    'qr',
    'qr_size',
    '300',
    'number',
    'Ukuran QR Code (px)',
    FALSE
),

-- Absensi
(
    'absensi',
    'batas_terlambat',
    '15',
    'number',
    'Batas Terlambat (menit)',
    FALSE
),
(
    'absensi',
    'batas_absen_masuk',
    '30',
    'number',
    'Batas Waktu Absen Masuk (menit)',
    FALSE
),
(
    'absensi',
    'batas_absen_keluar',
    '30',
    'number',
    'Batas Waktu Absen Keluar (menit)',
    FALSE
),
(
    'absensi',
    'wajib_selfie',
    'true',
    'boolean',
    'Wajib Foto Selfie',
    FALSE
),
(
    'absensi',
    'validasi_ketua_kelas',
    'true',
    'boolean',
    'Validasi Ketua Kelas',
    FALSE
),

-- Notifikasi
(
    'notifikasi',
    'push_enabled',
    'true',
    'boolean',
    'Push Notification Aktif',
    FALSE
),
(
    'notifikasi',
    'email_notification',
    'false',
    'boolean',
    'Email Notification Aktif',
    FALSE
),
(
    'notifikasi',
    'notif_sebelum_jadwal',
    '30',
    'number',
    'Notif Sebelum Jadwal (menit)',
    FALSE
),

-- Tahun Ajaran
(
    'akademik',
    'tahun_ajaran_aktif',
    '2024/2025',
    'string',
    'Tahun Ajaran Aktif',
    TRUE
),
(
    'akademik',
    'semester_aktif',
    'Ganjil',
    'string',
    'Semester Aktif',
    TRUE
),

-- PWA
(
    'pwa',
    'app_name',
    'Absensi Guru',
    'string',
    'Nama Aplikasi PWA',
    TRUE
),
(
    'pwa',
    'app_short_name',
    'Absensi',
    'string',
    'Nama Pendek PWA',
    TRUE
),
(
    'pwa',
    'app_version',
    '3.5.0',
    'string',
    'Versi Aplikasi',
    TRUE
),
(
    'pwa',
    'theme_color',
    '#007bff',
    'string',
    'Warna Theme',
    TRUE
);

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- SELESAI
-- ==========================================