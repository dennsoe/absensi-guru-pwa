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

-- Lanjutan di bagian 2...