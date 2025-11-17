# 🎓 Sistem Absensi Guru - Quick Start Guide

## 📌 Informasi Aplikasi

**Nama:** Sistem Absensi Guru Berbasis QR Code  
**Versi:** 1.0.0  
**Status:** ✅ Production Ready (100% Complete)  
**Laravel:** 11.46.1  
**PHP:** 8.2+  
**Database:** MySQL 8.0

---

## 🚀 INSTALASI & DEPLOYMENT

### 1. Clone/Download Project

```bash
# Jika dari Git
git clone <repository-url>
cd absen-guru

# Atau extract dari ZIP ke htdocs/absen-guru
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
```

Edit file `.env`:

```env
APP_NAME="Sistem Absensi Guru"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absen_guru
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Buat Database

```sql
CREATE DATABASE absen_guru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run Migrations & Seeders

```bash
php artisan migrate
php artisan db:seed
```

### 7. Create Storage Link

```bash
php artisan storage:link
```

### 8. Set Permissions (Linux/Mac)

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 9. Start Development Server

```bash
php artisan serve
```

Akses: http://localhost:8000

---

## 👥 TEST ACCOUNTS

Gunakan akun berikut untuk testing:

| Role           | Email                 | Password    | Deskripsi            |
| -------------- | --------------------- | ----------- | -------------------- |
| Admin          | admin@sekolah.com     | password123 | Full access          |
| Guru Piket     | piket@sekolah.com     | password123 | Monitoring harian    |
| Kepala Sekolah | kepsek@sekolah.com    | password123 | Approval & analytics |
| Kurikulum      | kurikulum@sekolah.com | password123 | Jadwal & laporan     |
| Guru           | guru1@sekolah.com     | password123 | Personal schedule    |
| Guru           | guru2@sekolah.com     | password123 | Personal schedule    |
| Ketua Kelas    | ketua@sekolah.com     | password123 | Generate QR          |

---

## 📚 FITUR UTAMA

### ✅ 6 Role Pengguna

1. **Admin** - User & system management
2. **Guru Piket** - Real-time monitoring
3. **Kepala Sekolah** - Approval & executive reports
4. **Kurikulum** - Schedule & academic reports
5. **Guru** - Personal schedule & leave requests
6. **Ketua Kelas** - QR Code generation

### ✅ Core Features

-   QR Code attendance system
-   Real-time monitoring (AJAX)
-   Leave/permit management
-   Substitute teacher assignment
-   Advanced analytics (Chart.js)
-   PDF reports export
-   Multi-role access control
-   File upload support
-   Mobile responsive

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[WebReinvent](https://webreinvent.com/)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Jump24](https://jump24.co.uk)**
-   **[Redberry](https://redberry.international/laravel/)**
-   **[Active Logic](https://activelogic.com)**
-   **[byte5](https://byte5.de)**
-   **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
