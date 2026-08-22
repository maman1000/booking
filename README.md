# 🏸 FutsalNow - Backend API

![Laravel](https://img.shields.io/badge/Laravel-11-red?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.4-blue?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?style=flat&logo=mysql)
![Railway](https://img.shields.io/badge/Deployed-Railway-0B0D0E?style=flat&logo=railway)

Backend API untuk aplikasi booking lapangan futsal **FutsalNow**. Dibangun dengan Laravel 11 dan MySQL.

🌐 **Base URL (Production):** `https://booking-production-xxxx.up.railway.app/api`  
📄 **Dokumentasi API:** [Link Postman](https://documenter.getpostman.com/view/48765304/2sBYArVtFJ)

---

## 📌 Fitur

| **Role** | **Fitur** |
|----------|-----------|
| **Public** | Lihat daftar lapangan, lihat slot tersedia |
| **Customer** | Registrasi, Login, Booking, Batalkan Booking, Bayar (dummy) |
| **Admin** | Dashboard, Kelola Booking, Kelola Layanan, Kelola Jadwal, Laporan |

---

## 🛠️ Tech Stack

| **Komponen** | **Teknologi** |
|--------------|---------------|
| Framework | Laravel 11 |
| Database | MySQL 8.0 |
| Authentication | Laravel Sanctum |
| API | RESTful |
| Deployment | Railway |

---

## 🚀 Instalasi Lokal

### 1. Clone Repository
```bash
git clone https://github.com/maman1000/booking.git
cd booking
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` sesuai database lokal:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=futsalnow_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Buat Database & Jalankan Migration
```bash
php artisan migrate:fresh --seed
```

### 5. Jalankan Server
```bash
php artisan serve
```

Aplikasi berjalan di `http://localhost:8000`

---

## 🌐 API Endpoints

> **📄 Dokumentasi lengkap:** [Link Postman](https://documenter.getpostman.com/view/48765304/2sBYArVtFJ)

### Public Endpoints
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/services` | Daftar semua lapangan |
| GET | `/api/services/{id}` | Detail lapangan |
| GET | `/api/services/{id}/available-slots?date=YYYY-MM-DD` | Slot tersedia |

### Customer Endpoints (auth:sanctum)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/register` | Registrasi user baru |
| POST | `/api/login` | Login |
| POST | `/api/logout` | Logout |
| POST | `/api/bookings` | Buat booking baru |
| GET | `/api/bookings/my` | Riwayat booking saya |
| PATCH | `/api/bookings/{id}/cancel` | Batalkan booking |
| POST | `/api/payments` | Bayar booking (dummy) |

### Admin Endpoints (auth:sanctum + role:admin)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/bookings` | Semua booking (dengan filter) |
| PATCH | `/api/bookings/{id}/status` | Ubah status booking |
| POST | `/api/services` | Tambah lapangan |
| PUT | `/api/services/{id}` | Edit lapangan |
| DELETE | `/api/services/{id}` | Hapus lapangan |
| GET | `/api/schedules` | Semua jadwal (dengan pagination) |
| POST | `/api/schedules` | Tambah jadwal |
| PUT | `/api/schedules/{id}` | Edit jadwal |
| PATCH | `/api/schedules/{id}/availability` | Buka/tutup slot |
| GET | `/api/reports/summary` | Dashboard summary |
| GET | `/api/reports/bookings` | Laporan booking |

---

## 📊 Database Schema

**Tabel utama:**
- `users` – Pengguna (customer & admin)
- `services` – Lapangan futsal
- `service_schedules` – Jadwal operasional per hari (0=Senin, 1=Selasa, ...)
- `bookings` – Data booking
- `payments` – Data pembayaran

---

## 🔧 Environment Variables

| **Variable** | **Deskripsi** | **Contoh** |
|--------------|---------------|------------|
| `APP_KEY` | Laravel application key | `base64:...` |
| `APP_ENV` | Environment mode | `production` |
| `APP_DEBUG` | Debug mode | `false` |
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_DATABASE` | Database name | `futsalnow_db` |
| `DB_USERNAME` | Database username | `root` |
| `DB_PASSWORD` | Database password | (kosong) |

---

## 🚀 Deployment ke Railway

1. Push repository ke GitHub.
2. Buat project baru di [Railway](https://railway.app) → Deploy from GitHub.
3. Tambahkan environment variables (lihat tabel di atas).
4. Railway akan otomatis menjalankan `composer install` dan build aplikasi.
5. Jalankan migration:
   ```bash
   php artisan migrate:fresh --seed
   ```

> **Catatan:** Pastikan database MySQL/PostgreSQL sudah terhubung di Railway.

---

## 📄 Lisensi

MIT © 2026 Maman Darusman

---

## 🙏 Kontribusi

Pull request dipersilakan. Untuk perubahan besar, buka issue terlebih dahulu.

---

## 📬 Kontak

- **Email:** [mamandarusman.st@gmail.com]
- **LinkedIn:** [https://www.linkedin.com/in/maman-darusman-88ba2696/]
- **Demo:** [https://futsalnow-fe.vercel.app](https://futsalnow-fe.vercel.app)
