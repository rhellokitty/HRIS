# Product Requirements Document (PRD)
**Project:** Sistem Informasi Manajemen Shift & Absensi Karyawan (Mini-HRIS)
**Context:** Tugas Mata Kuliah Sistem Informasi (Semester 1)
**Tech Stack:** Laravel 11, Blade Templating, Tailwind CSS, MySQL

## 1. Latar Belakang & Tujuan
Sistem ini dibangun untuk mendigitalisasi proses absensi dan penjadwalan shift karyawan yang sebelumnya dikelola secara manual dan rentan dimanipulasi oleh pihak yang tidak bertanggung jawab. Fokus utama adalah otomatisasi, validasi waktu dari sisi server, dan perekaman lokasi dasar.

## 2. User Roles & Hak Akses
Sistem memiliki 3 level pengguna (Role):
1. **Admin:** Memiliki kontrol penuh (Full Access) terhadap Master Data (User, Shift), persetujuan cuti, dan fitur generate jadwal shift.
2. **Operator:** Akses *Read-Only* ke Dashboard Monitoring untuk melihat data kehadiran harian dan jadwal aktif tanpa hak untuk mengubah data.
3. **Karyawan:** Akses mandiri untuk *clock-in/clock-out*, melihat jadwal/riwayat kehadiran sendiri, dan mengajukan form cuti/izin.

## 3. Spesifikasi Fitur Utama (Core Features)

### A. Modul Absensi (Anti-Manipulasi)
*   **Tombol Clock-in & Clock-out:** Menangkap otomatis koordinat lokasi pengguna (*Latitude & Longitude*) menggunakan `navigator.geolocation` HTML5.
*   **Server-Side Time Locking:** Waktu absen MUTLAK menggunakan timestamp dari server Laravel (`now()`). Input jam dari perangkat/browser klien diabaikan untuk mencegah manipulasi jam HP.
*   **Frontend Security:** Implementasi script JavaScript sederhana pada halaman absensi untuk menonaktifkan klik kanan (Context Menu) dan kombinasi tombol Inspect Element (F12, Ctrl+Shift+I).

### B. Modul Manajemen Shift
*   **CRUD Master Shift:** Penambahan/pengubahan data shift (Contoh: Shift Pagi 08:00 - 16:00).
*   **Auto-Generate Jadwal:** Terdapat satu tombol "Generate Jadwal Mingguan" di halaman Admin. Jika ditekan, sistem otomatis membuatkan *record* jadwal karyawan untuk 1 minggu ke depan berdasarkan pola shift sebelumnya (menghindari input manual satu per satu).

### C. Modul Pengajuan Cuti/Izin
*   **Form Karyawan:** Input tanggal mulai, tanggal selesai, jenis (Cuti/Izin/Sakit), dan keterangan/alasan.
*   **Tabel Approval Admin:** Admin dapat melakukan aksi "Terima" atau "Tolak" yang akan mengubah status pada riwayat pengajuan karyawan.

### D. Dashboard Monitoring
*   **Widget Ringkasan:** Menampilkan total karyawan hadir hari ini, total cuti/izin, dan karyawan yang terlambat.
*   **Tabel Live:** Menampilkan jadwal shift operasional yang sedang berjalan hari ini.

## 4. UI/UX & Layouting
*   **Sisi Karyawan (Mobile-First):** Layout dikunci di tengah layar (`max-w-md mx-auto`) menyerupai aplikasi mobile, agar optimal saat diakses menggunakan *smartphone* di lapangan.
*   **Sisi Admin & Operator (Desktop):** Menggunakan layout Dashboard standar (Sidebar di kiri, Konten di kanan) yang *responsive* untuk pengelolaan tabel data yang luas.

---

## 5. Struktur Database (Laravel Migrations)
Berikut adalah rancangan 5 tabel inti untuk di-generate oleh AI ke dalam file migration:

### 1. Tabel `users`
Menyimpan data master pengguna dan role.
*   `id` (Primary Key)
*   `nip` (String, Unique, Nullable)
*   `name` (String)
*   `email` (String, Unique)
*   `password` (String)
*   `role` (Enum: 'admin', 'operator', 'karyawan') - Default 'karyawan'
*   `timestamps`

### 2. Tabel `shifts`
Menyimpan master data jam kerja.
*   `id` (Primary Key)
*   `name` (String) -> cth: Pagi, Siang, Malam
*   `start_time` (Time)
*   `end_time` (Time)
*   `timestamps`

### 3. Tabel `schedules`
Menyimpan plotting jadwal karyawan per hari.
*   `id` (Primary Key)
*   `user_id` (Foreign Key ke `users`)
*   `shift_id` (Foreign Key ke `shifts`)
*   `date` (Date)
*   `timestamps`

### 4. Tabel `attendances`
Menyimpan data rekam absensi (waktu & koordinat).
*   `id` (Primary Key)
*   `user_id` (Foreign Key ke `users`)
*   `schedule_id` (Foreign Key ke `schedules`, Nullable)
*   `clock_in_time` (DateTime, Nullable)
*   `clock_out_time` (DateTime, Nullable)
*   `lat_in` (String, Nullable)
*   `lng_in` (String, Nullable)
*   `lat_out` (String, Nullable)
*   `lng_out` (String, Nullable)
*   `status` (Enum: 'hadir', 'telat', 'alpha') - Default 'alpha'
*   `timestamps`

### 5. Tabel `leave_requests`
Menyimpan pengajuan cuti/izin/sakit.
*   `id` (Primary Key)
*   `user_id` (Foreign Key ke `users`)
*   `start_date` (Date)
*   `end_date` (Date)
*   `type` (Enum: 'cuti', 'izin', 'sakit')
*   `reason` (Text)
*   `status` (Enum: 'pending', 'approved', 'rejected') - Default 'pending'
*   `timestamps`