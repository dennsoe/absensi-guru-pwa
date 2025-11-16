-- ==========================================
-- DATABASE: SISTEM ABSENSI GURU v3.5 (Part 2)
-- ==========================================

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