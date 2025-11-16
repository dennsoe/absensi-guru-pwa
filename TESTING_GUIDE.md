# Testing Guide - Aplikasi Absensi Guru

## 📋 Test Accounts

Database telah di-seed dengan akun testing berikut:

### 1. Administrator

-   **Username:** `admin`
-   **Password:** `admin123`
-   **Role:** Admin
-   **Akses:** Full access ke semua fitur admin

### 2. Kepala Sekolah

-   **Username:** `kepsek`
-   **Password:** `kepsek123`
-   **Role:** Kepala Sekolah
-   **Nama:** Dr. Bambang Sudrajat, S.Pd., M.Pd
-   **NIP:** 196805152000011001

### 3. Guru Piket

-   **Username:** `piket`
-   **Password:** `piket123`
-   **Role:** Guru Piket
-   **Nama:** Ahmad Fauzi, S.Pd
-   **NIP:** 197505152003011002

### 4. Kurikulum

-   **Username:** `kurikulum`
-   **Password:** `kurikulum123`
-   **Role:** Kurikulum
-   **Nama:** Siti Nurhaliza, S.Pd., M.Pd
-   **NIP:** 198005152008012003

### 5. Guru (RPL)

-   **Username:** `guru.rpl`
-   **Password:** `guru123`
-   **Role:** Guru
-   **Nama:** Dedi Suryadi, S.Kom
-   **NIP:** 198505152010011004
-   **Mengajar:** PWPB, PPB, BD, PKK

### 6. Guru (Matematika)

-   **Username:** `guru.mtk`
-   **Password:** `guru123`
-   **Role:** Guru
-   **Nama:** Rina Wati, S.Pd
-   **NIP:** 199005152015012005
-   **Mengajar:** Matematika

### 7. Ketua Kelas

-   **Username:** `ketua.rpl1`
-   **Password:** `ketua123`
-   **Role:** Ketua Kelas
-   **Nama:** Budi Santoso
-   **Kelas:** XII RPL 1

---

## 📚 Data Master

### Kelas

-   **XII RPL 1** - Rekayasa Perangkat Lunak
    -   Wali Kelas: Dedi Suryadi, S.Kom
    -   Ketua Kelas: Budi Santoso

### Mata Pelajaran

1. MTK - Matematika
2. BIN - Bahasa Indonesia
3. BING - Bahasa Inggris
4. FIS - Fisika
5. KIM - Kimia
6. PWPB - Pemrograman Web dan Perangkat Bergerak
7. PPB - Pemrograman Berorientasi Objek
8. BD - Basis Data
9. PKK - Produk Kreatif dan Kewirausahaan
10. PAI - Pendidikan Agama Islam

### Jadwal Mengajar (XII RPL 1)

#### Senin

-   07:30 - 09:00: PWPB (Dedi Suryadi) - Lab Komputer 1
-   09:15 - 10:45: Matematika (Rina Wati) - Ruang 12A

#### Selasa

-   07:30 - 09:00: PPB (Dedi Suryadi) - Lab Komputer 1
-   10:00 - 11:30: Matematika (Rina Wati) - Ruang 12A

#### Rabu

-   07:30 - 09:00: BD (Dedi Suryadi) - Lab Komputer 2

#### Kamis

-   07:30 - 09:00: PWPB (Dedi Suryadi) - Lab Komputer 1
-   10:00 - 11:30: PKK (Dedi Suryadi) - Ruang 12A

#### Jumat

-   07:30 - 09:00: Matematika (Rina Wati) - Ruang 12A

---

## 🧪 Testing Checklist

### A. User Management Testing

-   [ ] Login dengan setiap role
-   [ ] Test redirect sesuai role
-   [ ] Create user baru untuk setiap role:
    -   [ ] Admin (tidak perlu guru_id/kelas_id)
    -   [ ] Guru (tidak perlu guru_id/kelas_id)
    -   [ ] Guru Piket (perlu guru_id)
    -   [ ] Kepala Sekolah (perlu guru_id)
    -   [ ] Kurikulum (perlu guru_id)
    -   [ ] Ketua Kelas (perlu kelas_id)
-   [ ] Edit user dan verify data tersimpan
-   [ ] Delete user (check protection untuk admin terakhir)

### B. Kelas Management Testing

-   [ ] View daftar kelas
-   [ ] Create kelas baru dengan wali kelas dan ketua kelas
-   [ ] Edit kelas dan update wali/ketua kelas
-   [ ] Delete kelas dan verify users.kelas_id di-reset
-   [ ] Test filter by tingkat dan tahun ajaran

### C. Mata Pelajaran Management Testing

-   [ ] View daftar mata pelajaran
-   [ ] Create mata pelajaran baru
-   [ ] Edit mata pelajaran
-   [ ] Test unique kode_mapel validation
-   [ ] Try delete mata pelajaran yang digunakan di jadwal (harus gagal)
-   [ ] Delete mata pelajaran yang tidak digunakan

### D. Jadwal Mengajar Management Testing

-   [ ] View daftar jadwal
-   [ ] Create jadwal baru
-   [ ] Test conflict detection:
    -   [ ] Try create jadwal dengan guru sama, hari sama, waktu overlap
    -   [ ] Verify error message tampil
-   [ ] Edit jadwal
-   [ ] Delete jadwal
-   [ ] Test filters (guru, kelas, hari, tahun, status)

### E. QR Absensi Flow Testing

-   [ ] Login sebagai Ketua Kelas (ketua.rpl1)
-   [ ] Generate QR code untuk hari ini
-   [ ] Verify QR code tersimpan di database
-   [ ] Login sebagai Guru (guru.rpl atau guru.mtk)
-   [ ] Scan QR code
-   [ ] Verify absensi tersimpan dengan status hadir
-   [ ] Check waktu absen dan GPS coordinates (jika ada)

### F. Selfie Absensi Testing

-   [ ] Login sebagai Guru
-   [ ] Navigate ke halaman absensi selfie
-   [ ] Test camera access
-   [ ] Take selfie
-   [ ] Verify GPS validation (200m radius)
-   [ ] Submit absensi
-   [ ] Check foto tersimpan di storage
-   [ ] Verify record di database

### G. Laporan Absensi Testing

-   [ ] Login sebagai Admin
-   [ ] Test laporan utama:
    -   [ ] Filter by guru
    -   [ ] Filter by kelas
    -   [ ] Filter by status
    -   [ ] Filter by bulan/tahun
    -   [ ] Verify statistik card (total, hadir, terlambat, izin, alpha)
    -   [ ] Check pagination
-   [ ] Test laporan per guru:
    -   [ ] Select guru
    -   [ ] Select periode
    -   [ ] Verify data displayed
    -   [ ] Check statistics
-   [ ] Test laporan per kelas:
    -   [ ] Select kelas
    -   [ ] Select periode
    -   [ ] Verify grouped by guru
    -   [ ] Check statistics per guru

### H. Dashboard Testing

-   [ ] Login sebagai setiap role
-   [ ] Verify dashboard sesuai role:
    -   [ ] Admin: statistics cards, charts
    -   [ ] Guru: jadwal hari ini, absensi status
    -   [ ] Ketua Kelas: QR generation, absensi status
    -   [ ] Staff roles: appropriate dashboard

---

## 🔧 Testing Commands

### Reset Database

```bash
php artisan migrate:fresh --seed
```

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Check Routes

```bash
php artisan route:list --path=admin
php artisan route:list --path=guru
php artisan route:list --path=ketua-kelas
```

### Generate Storage Link

```bash
php artisan storage:link
```

---

## 📝 Expected Results

### User Creation Form Behavior

1. **Select Role = admin/guru:** Form hides "Profil Guru" and "Kelas" sections
2. **Select Role = guru_piket/kepala_sekolah/kurikulum:** Form shows "Profil Guru" dropdown
3. **Select Role = ketua_kelas:** Form shows "Kelas" dropdown

### Jadwal Conflict Detection

When creating jadwal, if guru already has schedule at same time on same day/semester/tahun:

-   Error message: "Terjadi bentrok jadwal! Guru sudah memiliki jadwal pada hari Senin jam 07:30 - 09:00"
-   Form tidak submit
-   User harus change time atau guru

### QR Code Flow

1. Ketua Kelas generates QR → QR saved to qr_codes table with expiry
2. Guru scans QR → System validates:
    - QR belum expired
    - Jadwal sesuai hari ini
    - GPS dalam radius 200m (if enabled)
3. Absensi created with status "hadir" atau "terlambat"

### Laporan Statistics

-   **Total:** Count all absensi
-   **Hadir:** Count where status = 'hadir'
-   **Terlambat:** Count where status = 'terlambat'
-   **Izin:** Count where status IN ('izin', 'sakit')
-   **Alpha:** Count where status = 'alpha'
-   **Persentase:** (hadir / total) \* 100

---

## 🐛 Known Issues / Notes

1. **Export functionality** masih placeholder (PDF/Excel)
2. **Grafik kehadiran** masih placeholder
3. **GPS validation** perlu testing dengan device yang support geolocation
4. **Camera access** perlu testing di browser yang support getUserMedia
5. **QR expiry** default 2 jam, bisa diatur di config

---

## 📞 Support

Jika menemukan bug atau issue saat testing:

1. Check error log: `storage/logs/laravel.log`
2. Check browser console untuk JavaScript errors
3. Verify database data dengan query manual
4. Clear cache dan try again

---

**Last Updated:** November 16, 2025
**Version:** 1.0.0
**Status:** Ready for Testing
