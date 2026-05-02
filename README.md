# Sistem Kepegawaian & Absensi (Microservices)

## Identitas
- **Nama**: Rafly Dzakki Pratama
- **NIM**: 2310511066
- **Kelas**: A
- **Studi Kasus**: Sistem Kepegawaian & Absensi
- **Provider OAuth Wajib**: Google OAuth 2.0

## Video Demonstrasi
**Link**: 

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
cd services/employee-service
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
Layanan akan berjalan di http://127.0.0.1:8000
###  Langkah 3: Menjalankan Auth Service & Report Service (Node.js)
Buka terminal kedua dan ketiga, lalu jalankan masing-masing layanan:
**Terminal 2 (Auth Service):**
cd services/auth-service
npm install
node index.js
Layanan akan berjalan di http://localhost:3001
**Terminal 3 (Report Service):**
cd services/report-service
npm install
node index.js
Layanan akan berjalan di http://localhost:3002
### Langkah 4: Menjalankan API Gateway
Buka terminal keempat sebagai pintu gerbang utama aplikasi:
cd gateway
npm install
node index.js
Gateway akan berjalan di http://localhost:3000. **Seluruh request dari Postman harus ditembakkan ke port 3000 ini**.

## Justifikasi Arsitektur: Microservices vs Monolitik
Dalam pengembangan perangkat lunak tradisional (Monolitik), seluruh fitur seperti otentikasi, manajemen pegawai, presensi, dan pelaporan disatukan dalam satu basis kode dan satu *database* besar. Namun, pada proyek ini, saya memutuskan untuk memisahkan fitur-fitur tersebut menjadi *microservices* mandiri dengan alasan berikut:
### 1. Isolasi Beban Kerja Otentikasi (Auth Service)
- **Monolitik:** Jika menggunakan monolitik, lonjakan antrean saat jam masuk kantor (di mana ratusan pegawai melakukan proses otentikasi secara bersamaan) dapat membebani *server* utama.
- **Microservices:** Memisahkan **Auth Service** memungkinkan layanan ini menangani proses kriptografi yang berat (seperti pembuatan JWT dan komunikasi eksternal dengan server Google OAuth) tanpa mengganggu performa layanan lain. Jika *Auth Service* mengalami kegagalan (*down*), layanan laporan masih bisa diakses secara internal.
### 2. Kinerja Komputasi Pembuatan Laporan (Report Service)
- **Monolitik:** Proses agregasi data besar untuk laporan akhir bulan (menghitung total hadir, telat, absen, dan cuti dari ratusan pegawai) sangat memakan memori (RAM) dan CPU. Jika dieksekusi di *server* monolitik, proses ini berpotensi membuat fitur *clock-in* menjadi lambat atau *timeout*.
- **Microservices:** Dengan memisahkan **Report Service** menggunakan Node.js, perhitungan komputasi berat diisolasi. *Service* ini dapat menarik data dari *Employee Service* secara *background process* (menggunakan parameter paginasi khusus per\_page=all) tanpa memblokir interaksi pengguna lain yang sedang menggunakan sistem absensi utama.
### 3. Kebebasan Pemilihan Teknologi (Polyglot Persistence)
Pendekatan *microservices* memungkinkan pemilihan teknologi (*stack*) terbaik untuk setiap spesifikasi tugas:
- **Laravel (Employee Service):** Dipilih karena memiliki ORM (Eloquent) dan validasi yang sangat matang, sehingga sangat efisien untuk menangani relasi basis data kompleks (seperti relasi *Employee*, *Position*, dan *Attendance*).
- **Node.js + Express (Gateway, Auth, Report):** Dipilih karena arsitektur *non-blocking I/O* miliknya sangat cepat dan ringan untuk menangani *routing proxy*, I/O jaringan antar-API, serta integrasi pustaka pihak ketiga seperti google-auth-library.
