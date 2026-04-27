# Sistem Kepegawaian & Absensi (Microservices)

## Identitas
- **Nama**: Rafly Dzakki Pratama
- **NIM**: 2310511066
- **Kelas**: A
- **Studi Kasus**: Sistem Kepegawaian & Absensi
- **Provider OAuth Wajib**: Google OAuth 2.0

## Rencana Arsitektur
Sistem ini dibangun dengan arsitektur *microservices* yang terdiri dari:
1. **API Gateway**: *Single entry point* dan *routing*.
2. **Auth Service**: Menangani otentikasi JWT dan Google OAuth 2.0.
3. **Employee Service**: Menangani manajemen data pegawai dan pengajuan cuti (PHP MVC - Laravel 11).
4. **Attendance Service**: Menangani *clock-in*, *clock-out*, dan rekap absensi.

## Cara Menjalankan
