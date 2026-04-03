<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" />
  <img src="https://img.shields.io/badge/face--api.js-AI-00dc82?style=for-the-badge&logo=tensorflow&logoColor=white" />
  <img src="https://img.shields.io/badge/SQLite-Database-003B57?style=for-the-badge&logo=sqlite&logoColor=white" />
  <img src="https://img.shields.io/badge/Vite-8-646CFF?style=for-the-badge&logo=vite&logoColor=white" />
</p>

<h1 align="center">Gojira Vision</h1>
<h3 align="center">Sistem Absensi Karyawan Berbasis Pengenalan Wajah</h3>

<p align="center">
  <i>Absensi cerdas tanpa kartu, tanpa sidik jari — cukup tunjukkan wajahmu.</i>
</p>

---

## Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Demo Akun](#-demo-akun)
- [Fitur Utama](#-fitur-utama)
- [Tech Stack](#-tech-stack)
- [Arsitektur Sistem](#-arsitektur-sistem)
- [Cara Kerja Face Recognition](#-cara-kerja-face-recognition)
- [Instalasi](#-instalasi)
- [Struktur Database](#-struktur-database)
- [Kelebihan](#-kelebihan)
- [Kekurangan](#-kekurangan)
- [Roadmap](#-roadmap)
- [Struktur Folder](#-struktur-folder)

---

## Tentang Proyek

**Gojira Vision** adalah aplikasi web sistem absensi karyawan yang menggunakan teknologi **pengenalan wajah (face recognition)** berbasis browser. Sistem ini dibangun dengan **Laravel 13** sebagai backend dan **face-api.js** untuk proses deteksi & pengenalan wajah secara real-time melalui webcam.

Terdapat dua role pengguna:

| Role | Akses |
|------|-------|
| **Admin** | Mengelola karyawan, departemen, jadwal kerja, dan laporan absensi |
| **Karyawan** | Registrasi wajah, clock-in/out dengan verifikasi wajah, lihat riwayat |

Selain itu, tersedia **Kiosk Mode** — mode terminal publik yang memungkinkan absensi otomatis tanpa login, cocok untuk dipasang di pintu masuk kantor.

---

## Demo Akun

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@gojiravision.com` | `password` |
| Karyawan | `budi@gojiravision.com` | `password` |

> Jalankan `php artisan db:seed` untuk membuat akun demo di atas.

---

## Fitur Utama

### Untuk Admin

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Statistik real-time: total karyawan, hadir hari ini, tidak hadir, terlambat + 10 absensi terakhir |
| **Kelola Karyawan** | CRUD lengkap — tambah, edit, hapus karyawan beserta departemen & posisi |
| **Kelola Departemen** | CRUD departemen dengan jumlah karyawan per departemen |
| **Kelola Jadwal Kerja** | Atur jadwal 7 hari per minggu untuk banyak karyawan sekaligus (bulk update) + toggle hari libur |
| **Laporan Absensi** | Filter berdasarkan tanggal dan nama karyawan |

### Untuk Karyawan

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Statistik bulanan: total hadir, total terlambat, plus status absensi hari ini |
| **Registrasi Wajah** | Capture 3 foto wajah via webcam untuk generate face descriptor |
| **Clock-In / Clock-Out** | Absensi dengan verifikasi wajah — sistem memastikan wajah yang terdeteksi sesuai akun yang login |
| **Riwayat Absensi** | Lihat riwayat absensi lengkap dengan pagination |

### Mode Kiosk (Publik)

| Fitur | Deskripsi |
|-------|-----------|
| **Auto-Detection** | Deteksi wajah otomatis tanpa perlu login |
| **Auto Clock-In/Out** | Absensi otomatis saat wajah dikenali |
| **Cooldown 30 Detik** | Mencegah duplikasi absensi per karyawan |
| **Activity Log** | Log aktivitas real-time di layar |
| **Live Clock** | Jam digital real-time |
| **Bounding Box** | Kotak hijau (dikenali) / merah (tidak dikenali) pada wajah |

### Fitur Umum

| Fitur | Deskripsi |
|-------|-----------|
| **Dark / Light Theme** | Toggle tema gelap/terang, tersimpan di `localStorage` |
| **Auto Status Detection** | Otomatis menentukan: `hadir`/`terlambat` saat clock-in, `tepat_waktu`/`pulang_cepat`/`lembur` saat clock-out |
| **Role-Based Access** | Middleware memastikan admin & karyawan hanya akses halaman masing-masing |
| **Responsive UI** | Layout sidebar + konten utama, fully themed dengan Bootstrap 5 |

---

## Tech Stack

```
┌─────────────────────────────────────────────────────────────┐
│                       FRONTEND                              │
│                                                             │
│   Bootstrap 5.3 ─── Responsive UI & Components             │
│   face-api.js ────── Face Detection & Recognition (Browser) │
│   Vite 8 ─────────── Asset Bundling & HMR                  │
│   Axios ──────────── HTTP Client                            │
│   Sass ───────────── CSS Pre-processing                     │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                       BACKEND                               │
│                                                             │
│   PHP 8.3+ ───────── Runtime                                │
│   Laravel 13 ─────── Framework                              │
│   Blade ──────────── Templating Engine                      │
│   Eloquent ORM ───── Database Abstraction                   │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                       DATABASE                              │
│                                                             │
│   SQLite ─────────── Default (MySQL juga didukung)          │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                     AI / ML (Client-Side)                   │
│                                                             │
│   TinyFaceDetector ─ Deteksi wajah ringan                   │
│   FaceLandmark68 ─── 68 titik landmark wajah                │
│   FaceRecognition ── 128-dim face descriptor vector          │
│   FaceMatcher ────── Pencocokan wajah (threshold: 0.6)      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## Arsitektur Sistem

```
┌──────────┐    ┌──────────────┐    ┌─────────────────┐
│  Browser  │───▶│   Webcam API  │───▶│  face-api.js    │
│ (Client)  │    │  getUserMedia │    │  (TensorFlow)   │
└─────┬─────┘    └──────────────┘    └────────┬────────┘
      │                                        │
      │  HTTP Request                          │ Face Descriptor
      │  (Clock-in/out + user_id)              │ (128-dim vector)
      ▼                                        ▼
┌─────────────────────────────────────────────────────────────┐
│                    Laravel Backend                           │
│                                                             │
│  ┌─────────────┐  ┌──────────────┐  ┌───────────────────┐  │
│  │   Routes     │─▶│ Controllers   │─▶│     Models        │  │
│  │  (web.php)   │  │              │  │  (Eloquent ORM)   │  │
│  └─────────────┘  └──────────────┘  └─────────┬─────────┘  │
│                                                │            │
│  ┌─────────────┐  ┌──────────────┐            │            │
│  │  Middleware   │  │    Views     │            │            │
│  │ (RoleCheck)  │  │   (Blade)    │            │            │
│  └─────────────┘  └──────────────┘            │            │
│                                                ▼            │
│                                     ┌──────────────────┐   │
│                                     │  SQLite Database  │   │
│                                     └──────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## Cara Kerja Face Recognition

### 1. Registrasi Wajah

```
Karyawan membuka halaman registrasi
        │
        ▼
Webcam aktif ──▶ Capture 3 foto wajah
        │
        ▼
face-api.js extract 128-dim descriptor per foto
        │
        ▼
POST ke server ──▶ Simpan 3 descriptor di tabel face_descriptors
```

### 2. Absensi (Mode Login)

```
Karyawan buka halaman absensi
        │
        ▼
Fetch semua face descriptors dari API
        │
        ▼
Buat FaceMatcher (threshold 0.6)
        │
        ▼
Setiap 500ms: deteksi wajah dari webcam
        │
        ├── Match (distance < 0.6) ──▶ Verifikasi = user yang login?
        │                                    │
        │                              ├── Ya ──▶ Boleh clock-in/out
        │                              └── Tidak ──▶ Ditolak
        │
        └── No Match ──▶ "Wajah tidak dikenali"
```

### 3. Mode Kiosk (Tanpa Login)

```
Terminal publik menjalankan halaman kiosk
        │
        ▼
Webcam aktif ──▶ Fetch semua descriptors
        │
        ▼
Deteksi & cocokkan wajah secara otomatis
        │
        ▼
Wajah dikenali ──▶ Auto clock-in / clock-out
        │
        ▼
Cooldown 30 detik per karyawan (anti duplikasi)
```

---

## Instalasi

### Prasyarat

- PHP >= 8.3
- Composer
- Node.js >= 18
- npm

### Langkah-langkah

```bash
# 1. Clone repository
git clone https://github.com/username/gojira-vision.git
cd gojira-vision

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Buat database SQLite
touch database/database.sqlite

# 5. Jalankan migrasi & seeder
php artisan migrate --seed

# 6. Build frontend assets
npm run build
# atau untuk development:
npm run dev

# 7. Jalankan server
php artisan serve
```

Buka `http://localhost:8000` dan login dengan akun demo.

### Konfigurasi Opsional

| Setting | File | Keterangan |
|---------|------|------------|
| Database MySQL | `.env` | Ubah `DB_CONNECTION=mysql` dan isi kredensial |
| Queue Worker | Terminal | Jalankan `php artisan queue:work` untuk background jobs |

---

## Struktur Database

```
┌─────────────────┐       ┌──────────────────┐
│   departments    │       │   work_schedules │
│─────────────────│       │──────────────────│
│ id               │       │ id               │
│ name (unique)    │       │ user_id (FK)     │
└────────┬────────┘       │ day_of_week (1-7)│
         │                 │ clock_in_time    │
         │ 1:N             │ clock_out_time   │
         │                 │ is_day_off       │
         ▼                 └────────┬─────────┘
┌─────────────────┐                │
│     users        │◀──────────────┘
│─────────────────│         N:1
│ id               │
│ name             │       ┌──────────────────┐
│ email            │       │ face_descriptors  │
│ password         │       │──────────────────│
│ role (enum)      │──────▶│ id               │
│ position         │  1:N  │ user_id (FK)     │
│ phone            │       │ descriptor (JSON) │
│ face_registered  │       │ label            │
│ department_id    │       └──────────────────┘
└────────┬────────┘
         │
         │ 1:N
         ▼
┌─────────────────┐
│   attendances    │
│─────────────────│
│ id               │
│ user_id (FK)     │
│ date             │
│ clock_in         │
│ clock_out        │
│ status           │  ◀── hadir / terlambat / alpha
│ clock_out_status │  ◀── tepat_waktu / pulang_cepat / lembur
│ note             │
└─────────────────┘
```

---

## Kelebihan

| # | Kelebihan | Penjelasan |
|---|-----------|------------|
| 1 | **Contactless & Hygienic** | Tidak perlu sentuh perangkat apapun — cukup wajah di depan webcam. Cocok untuk era pasca-pandemi |
| 2 | **Anti-Titip Absen** | Face verification memastikan yang absen benar-benar orangnya, bukan teman kerja |
| 3 | **Zero Hardware Cost (AI)** | Face recognition berjalan di browser (face-api.js) — tidak butuh server GPU atau API berbayar |
| 4 | **Kiosk Mode** | Terminal publik tanpa login — tinggal pasang laptop/tablet di pintu masuk, karyawan lewat langsung terabsen |
| 5 | **Auto Status Detection** | Sistem otomatis menentukan terlambat/tepat waktu/lembur berdasarkan jadwal kerja |
| 6 | **Flexible Scheduling** | Jadwal kerja per karyawan per hari — bisa atur shift berbeda tiap hari |
| 7 | **Bulk Schedule Update** | Admin bisa atur jadwal banyak karyawan sekaligus |
| 8 | **Dark & Light Theme** | UI modern dengan dua tema, tersimpan permanen di browser |
| 9 | **Ringan & Portabel** | SQLite sebagai default database — tidak perlu install MySQL server |
| 10 | **Role-Based Access Control** | Pemisahan akses yang jelas antara admin dan karyawan |
| 11 | **Multiple Face Samples** | 3 foto wajah saat registrasi meningkatkan akurasi pengenalan |
| 12 | **Open Source & Customizable** | Mudah dimodifikasi sesuai kebutuhan organisasi |

---

## Kekurangan

| # | Kekurangan | Penjelasan |
|---|------------|------------|
| 1 | **Client-Side Processing** | Semua proses AI berjalan di browser — performa tergantung spesifikasi device pengguna |
| 2 | **Tidak Ada Liveness Detection** | Belum ada deteksi apakah wajah itu "hidup" atau hanya foto/video — rentan spoofing |
| 3 | **Butuh Koneksi Internet** | Model face-api.js dimuat dari CDN — tanpa internet, face recognition tidak berjalan |
| 4 | **Kiosk Tanpa Auth** | Kiosk mode bersifat public endpoint — siapapun yang tahu URL bisa mengaksesnya |
| 5 | **Skalabilitas Terbatas** | SQLite kurang optimal untuk ratusan/ribuan karyawan concurrent |
| 6 | **Tidak Ada Notifikasi** | Belum ada fitur notifikasi email/push saat karyawan terlambat atau tidak hadir |
| 7 | **Tidak Ada Export Data** | Belum bisa export laporan absensi ke Excel/PDF |
| 8 | **Single Location** | Belum mendukung multi-kantor atau multi-lokasi |
| 9 | **Tidak Ada API Mobile** | Belum ada REST API untuk aplikasi mobile |
| 10 | **Akurasi Bergantung Kondisi** | Pencahayaan buruk, angle wajah, atau perubahan penampilan bisa menurunkan akurasi |

---

## Roadmap

- [ ] Liveness Detection (anti-spoofing)
- [ ] Export laporan ke Excel & PDF
- [ ] Notifikasi email untuk keterlambatan
- [ ] REST API untuk mobile app
- [ ] Multi-lokasi & multi-timezone
- [ ] Self-hosted face-api.js models (offline support)
- [ ] Dashboard analytics yang lebih detail
- [ ] Integrasi dengan Google Calendar / Slack

---

## Struktur Folder

```
gojira-vision/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Dashboard, Employee, Department, Schedule, Report
│   │   │   ├── AuthController
│   │   │   ├── AttendanceController
│   │   │   ├── FaceRegistrationController
│   │   │   ├── KaryawanController
│   │   │   └── KioskController
│   │   └── Middleware/
│   │       └── RoleMiddleware
│   └── Models/                     # User, Attendance, FaceDescriptor, Department, WorkSchedule
├── database/
│   ├── migrations/                 # 9 migration files
│   └── seeders/
│       └── DatabaseSeeder          # Admin + Karyawan demo
├── resources/
│   ├── css/app.css                 # 1000+ baris custom dark/light theme
│   ├── js/app.js
│   └── views/
│       ├── layouts/app.blade.php   # Main layout dengan sidebar
│       ├── auth/login.blade.php
│       ├── admin/                  # 6 blade views
│       ├── karyawan/               # 4 blade views
│       └── kiosk.blade.php         # Full-screen kiosk mode
├── routes/
│   └── web.php                     # Semua route definitions
├── .env.example
├── composer.json
├── package.json
└── vite.config.js
```

---

<p align="center">
  <b>Gojira Vision</b> — Smart Attendance, Zero Touch.
  <br/>
  <sub>Built with Laravel 13 & face-api.js</sub>
</p>
