# Sistem Kepegawaian & Absensi (Microservices)

## Identitas
- **Nama**: Rafly Dzakki Pratama
- **NIM**: 2310511066
- **Kelas**: A
- **Studi Kasus**: Sistem Kepegawaian & Absensi
- **Provider OAuth Wajib**: Google OAuth 2.0

## Video Demonstrasi
**Link**: https://drive.google.com/file/d/1yZv5C4UZuOu9Zjpf0hTiJpMVf7u-_Ma2/view?usp=sharing

## Rencana Arsitektur
Sistem ini dibangun dengan arsitektur *microservices* yang terdiri dari:
1. **API Gateway**: *Single entry point* (Port 3000) dan *routing* pusat untuk semua request.
2. **Auth Service**: Menangani otentikasi (Local & Google OAuth 2.0) serta manajemen JWT (Port 3001).
3. **Employee & Attendance Service**: Layanan inti berbasis PHP/Laravel 11 untuk manajemen data pegawai, jabatan, pengajuan cuti, serta *clock-in*/*clock-out* otomatis (Port 8000).
4. **Report Service**: Layanan khusus Node.js untuk kalkulasi agregat dan pembuatan laporan absensi bulanan secara asinkron (Port 3002).

## Persyaratan Sistem (Prerequisites)
Sebelum menjalankan aplikasi, pastikan sistem Anda memiliki komponen berikut:
- PHP >= 8.3.30 & Composer
- Node.js >= 24.13.1
- MySQL (XAMPP / Laragon)
- Postman (untuk pengujian API)

## Cara Menjalankan
Mengingat pendekatan sistem terdistribusi, setiap *service* harus dijalankan pada terminal yang terpisah.
### Langkah 1: Persiapan Database
1. Nyalakan modul **MySQL** pada XAMPP/Laragon.
2. Buat tiga *database* baru melalui phpMyAdmin atau terminal:
   1. uts\_auth\_db
   1. uts\_employee\_db
   1. uts\_report\_db
### Langkah 2: Menjalankan Employee Service (Laravel)
Buka terminal pertama dan arahkan ke direktori layanan pegawai:
- cd services/employee-service
- composer install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate --seed
- php artisan serve
- Layanan akan berjalan di http://127.0.0.1:8000
###  Langkah 3: Menjalankan Auth Service & Report Service (Node.js)
Buka terminal kedua dan ketiga, lalu jalankan masing-masing layanan:
**Terminal 2 (Auth Service):**
- cd services/auth-service
- npm install
- node index.js
- Layanan akan berjalan di http://localhost:3001
**Terminal 3 (Report Service):**
- cd services/report-service
- npm install
- node index.js
- Layanan akan berjalan di http://localhost:3002
### Langkah 4: Menjalankan API Gateway
Buka terminal keempat sebagai pintu gerbang utama aplikasi:
- cd gateway
- npm install
- node index.js
- Gateway akan berjalan di http://localhost:3000. 
**Seluruh request dari Postman harus ditembakkan ke port 3000 ini**.