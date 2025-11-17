# ✅ TESTING CHECKLIST - Sistem Absensi Guru

## 🎯 PRE-TESTING SETUP

### Database & Environment

-   [ ] Database `absen_guru` created
-   [ ] Migrations run successfully (`php artisan migrate`)
-   [ ] Seeders run successfully (`php artisan db:seed`)
-   [ ] Storage link created (`php artisan storage:link`)
-   [ ] .env configured correctly
-   [ ] Server running (`php artisan serve`)

---

## 👤 AUTHENTICATION TESTING

### Login Testing

-   [ ] Admin login: admin@sekolah.com / password123
-   [ ] Guru Piket login: piket@sekolah.com / password123
-   [ ] Kepala Sekolah login: kepsek@sekolah.com / password123
-   [ ] Kurikulum login: kurikulum@sekolah.com / password123
-   [ ] Guru 1 login: guru1@sekolah.com / password123
-   [ ] Guru 2 login: guru2@sekolah.com / password123
-   [ ] Ketua Kelas login: ketua@sekolah.com / password123

### Logout & Session

-   [ ] Logout works correctly
-   [ ] Session persists properly
-   [ ] Redirect to dashboard after login
-   [ ] Redirect to login when not authenticated

---

## 🔒 AUTHORIZATION TESTING

### Role Access Control

-   [ ] Admin can access admin routes only
-   [ ] Guru Piket can access guru-piket routes only
-   [ ] Kepala Sekolah can access kepala-sekolah routes only
-   [ ] Kurikulum can access kurikulum routes only
-   [ ] Guru can access guru routes only
-   [ ] Ketua Kelas can access ketua-kelas routes only
-   [ ] Unauthorized access redirects properly

---

## 👨‍💼 ADMIN FEATURES

### User Management

-   [ ] View users list
-   [ ] Create new user
-   [ ] Edit existing user
-   [ ] Delete user
-   [ ] Search user works
-   [ ] Pagination works

### Guru Management

-   [ ] View guru list
-   [ ] Create new guru
-   [ ] Edit existing guru
-   [ ] Delete guru
-   [ ] Search guru works

### Kelas Management

-   [ ] View kelas list
-   [ ] Create new kelas
-   [ ] Edit existing kelas
-   [ ] Delete kelas

### Mata Pelajaran Management

-   [ ] View mapel list
-   [ ] Create new mapel
-   [ ] Edit existing mapel
-   [ ] Delete mapel

---

## 👮 GURU PIKET FEATURES

### Monitoring Dashboard

-   [ ] Dashboard loads correctly
-   [ ] Statistics cards show data
-   [ ] Guru list displays
-   [ ] Status badges show correctly
-   [ ] AJAX auto-refresh works (30s)
-   [ ] Search/filter works
-   [ ] Detail view shows guru info
-   [ ] Detail view shows schedule

### Laporan Harian

-   [ ] Daily report page loads
-   [ ] Summary statistics correct
-   [ ] Date filter works
-   [ ] Guru breakdown table displays
-   [ ] Status counts accurate
-   [ ] Warning section shows issues

### Kontak Guru

-   [ ] Contact directory loads
-   [ ] Guru list displays with contact info
-   [ ] Search function works
-   [ ] WhatsApp link works
-   [ ] Email link works

---

## 🏫 KEPALA SEKOLAH FEATURES

### Monitoring Dashboard

-   [ ] Dashboard loads with charts
-   [ ] 30-day trend chart displays
-   [ ] Statistics cards accurate
-   [ ] Top violations table shows
-   [ ] Export button available

### Approval Izin/Cuti

-   [ ] Approval list displays
-   [ ] Filters work (status, guru)
-   [ ] Pagination works
-   [ ] Detail view shows complete info
-   [ ] 30-day attendance history shows
-   [ ] File attachment viewable
-   [ ] Approve action works
-   [ ] Reject action works
-   [ ] Catatan approval saves

### Laporan Bulanan

-   [ ] Monthly report loads
-   [ ] Month/year filter works
-   [ ] Summary cards accurate
-   [ ] Per-guru breakdown displays
-   [ ] Percentage calculations correct
-   [ ] Color coding works (green/yellow/red)
-   [ ] Link to analytics works

### Analytics Dashboard

-   [ ] Analytics page loads
-   [ ] 30-day trend chart renders
-   [ ] 6-month comparison chart renders
-   [ ] Day-of-week stats chart renders
-   [ ] Top 10 performers table displays
-   [ ] Top 10 violations table displays
-   [ ] Charts update with filter

---

## 📚 KURIKULUM FEATURES

### Jadwal Mengajar Management

-   [ ] Jadwal list displays
-   [ ] Multi-filter works (guru, kelas, hari, tahun_ajaran)
-   [ ] Create jadwal form loads
-   [ ] Create jadwal saves successfully
-   [ ] Edit jadwal form loads with data
-   [ ] Update jadwal works
-   [ ] Delete jadwal works (with confirmation)
-   [ ] Conflict detection message shows
-   [ ] Status badge displays correctly

### Guru Pengganti

-   [ ] Guru pengganti list displays
-   [ ] Filter works (status, tanggal, guru)
-   [ ] Create form loads
-   [ ] Jadwal dropdown populates
-   [ ] Guru asli auto-fills when jadwal selected
-   [ ] Create pengganti saves
-   [ ] Notification sent (check if implemented)
-   [ ] Delete pengganti works
-   [ ] Status updates correctly

### Approval Jadwal

-   [ ] Approval list displays
-   [ ] Filter works (status, guru, tahun_ajaran)
-   [ ] Statistics cards accurate
-   [ ] Approve action works
-   [ ] Reject action works
-   [ ] Status updates in database

### Laporan Akademik

-   [ ] Main laporan page loads
-   [ ] Statistics cards accurate
-   [ ] Recent activity displays
-   [ ] Per-guru report loads
-   [ ] Per-guru filters work
-   [ ] Per-guru data accurate
-   [ ] Percentage calculations correct
-   [ ] Performance status shows
-   [ ] Per-mapel report loads
-   [ ] Per-mapel filters work
-   [ ] Per-mapel data accurate
-   [ ] Top performers section displays
-   [ ] PDF export works
-   [ ] PDF format correct
-   [ ] PDF data accurate

---

## 👨‍🏫 GURU FEATURES

### Jadwal Pribadi

-   [ ] Jadwal list displays
-   [ ] Statistics cards accurate
-   [ ] Filter works (hari, tahun_ajaran, semester)
-   [ ] Jadwal grouped by day
-   [ ] Today's schedule loads
-   [ ] Current schedule highlighted
-   [ ] Status badges correct (berlangsung/akan datang/selesai)
-   [ ] Absen status shows
-   [ ] Statistics accurate

### Izin/Cuti Management

-   [ ] Izin list displays
-   [ ] Statistics cards accurate (pending/approved/rejected)
-   [ ] Filter works (status, jenis)
-   [ ] Create form loads
-   [ ] Jenis dropdown works (izin/cuti/sakit)
-   [ ] Date validation works
-   [ ] File upload works
-   [ ] Create izin saves
-   [ ] Edit form loads (pending only)
-   [ ] Update izin works
-   [ ] Delete izin works (pending only)
-   [ ] Show detail displays all info
-   [ ] File download works
-   [ ] Timeline shows correctly
-   [ ] Approval notes display (if approved/rejected)

### Profile Management

-   [ ] Profile page loads
-   [ ] Photo displays (or placeholder)
-   [ ] Statistics accurate
-   [ ] Contact info shows
-   [ ] Recent attendance (7 days) displays
-   [ ] Edit profile form loads
-   [ ] Photo preview works
-   [ ] Update profile saves
-   [ ] Photo upload works
-   [ ] Change password form loads
-   [ ] Password validation works
-   [ ] Change password successful
-   [ ] Session logout after password change

---

## 🎓 KETUA KELAS FEATURES

### QR Code Generation

-   [ ] QR Code generator page loads
-   [ ] Kelas selection works
-   [ ] QR Code generates
-   [ ] QR Code scannable
-   [ ] Download QR Code works

---

## 🔔 API TESTING (Optional)

### Notification API

-   [ ] GET /api/notifications returns data
-   [ ] POST /api/notifications/mark-read works
-   [ ] Authentication required

### Absensi API

-   [ ] POST /api/absensi/scan works
-   [ ] QR Code validation works
-   [ ] Timestamp recorded

### Settings API

-   [ ] GET /api/settings returns config
-   [ ] PUT /api/settings updates settings

---

## 📊 UI/UX TESTING

### Responsive Design

-   [ ] Desktop view (1920x1080)
-   [ ] Laptop view (1366x768)
-   [ ] Tablet view (768x1024)
-   [ ] Mobile view (375x667)

### Components

-   [ ] Navigation menu works
-   [ ] Breadcrumbs display
-   [ ] Status badges color-coded
-   [ ] Cards render properly
-   [ ] Tables responsive
-   [ ] Forms styled correctly
-   [ ] Buttons functional
-   [ ] Icons display

### Interactions

-   [ ] Forms submit correctly
-   [ ] Validation errors show
-   [ ] Success messages display
-   [ ] Confirmation dialogs work
-   [ ] Loading states show
-   [ ] Pagination works
-   [ ] Search/filter instant
-   [ ] Date pickers work

### Charts (Chart.js)

-   [ ] Line charts render
-   [ ] Bar charts render
-   [ ] Charts responsive
-   [ ] Legend displays
-   [ ] Tooltips work
-   [ ] Data accurate

---

## 🔐 SECURITY TESTING

### Basic Security

-   [ ] CSRF protection active
-   [ ] XSS prevention works
-   [ ] SQL injection prevented
-   [ ] Password hashing works
-   [ ] File upload validation works
-   [ ] Session timeout works

### Authorization

-   [ ] Direct URL access blocked for unauthorized roles
-   [ ] API endpoints require authentication
-   [ ] File access requires authentication

---

## 📁 FILE OPERATIONS

### File Upload

-   [ ] Photo upload works (guru profile)
-   [ ] File upload works (izin attachment)
-   [ ] File validation works (size, type)
-   [ ] Files stored in storage/app/public
-   [ ] Files accessible via Storage::url()

### File Download

-   [ ] PDF download works
-   [ ] Attachment download works
-   [ ] File permissions correct

---

## 🗄️ DATABASE TESTING

### Data Integrity

-   [ ] Foreign keys enforced
-   [ ] Cascade delete works
-   [ ] Timestamps recorded
-   [ ] Default values correct
-   [ ] Nullable fields work

### Relationships

-   [ ] User → Guru relationship
-   [ ] Guru → Absensi relationship
-   [ ] JadwalMengajar → relationships
-   [ ] IzinCuti → relationships
-   [ ] GuruPengganti → relationships
-   [ ] Notification → User relationship

---

## 🚀 PERFORMANCE TESTING

### Page Load Speed

-   [ ] Dashboard loads < 2s
-   [ ] List pages load < 2s
-   [ ] Charts render < 1s
-   [ ] AJAX requests < 1s

### Database Queries

-   [ ] N+1 query problem avoided (use eager loading)
-   [ ] Indexes used for frequent queries
-   [ ] Pagination limits records

---

## 🐛 ERROR HANDLING

### Expected Errors

-   [ ] 404 page works
-   [ ] 403 unauthorized page works
-   [ ] 500 error page works (disable debug to test)
-   [ ] Validation errors display
-   [ ] Database errors handled

### Edge Cases

-   [ ] Empty data lists show message
-   [ ] Missing relationships handled
-   [ ] Null values handled
-   [ ] Date edge cases work

---

## 📝 NOTES & ISSUES

### Issues Found

1. ***
2. ***
3. ***

### Improvements Needed

1. ***
2. ***
3. ***

### Additional Testing Required

1. ***
2. ***
3. ***

---

## ✅ FINAL SIGN-OFF

**Tested By:** ********\_\_\_********  
**Date:** ********\_\_\_********  
**Overall Status:** [ ] PASS / [ ] FAIL  
**Ready for Production:** [ ] YES / [ ] NO

**Comments:**

---

---

---

---

**Testing Complete!** 🎉
