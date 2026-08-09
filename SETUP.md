# SETUP.md — Backend booking-api (Laravel API)

> **Catatan:** semua langkah di dokumen ini **sudah diterapkan** pada folder `booking-api/`.
> Untuk sekadar menjalankan project, cukup `./setup.sh` lalu `./dev.sh` dari root repo —
> lihat [README.md](../README.md#cara-menjalankan). Dokumen ini disimpan sebagai referensi
> kalau kamu ingin merakit ulang backend dari project Laravel kosong.

Folder `booking-api/` ini awalnya adalah **overlay** di atas project Laravel fresh. Ikuti
langkah berikut persis agar backend berjalan.

## 1. Buat project Laravel baru

```bash
composer create-project laravel/laravel booking-api "11.*"
# atau versi terbaru:
# composer create-project laravel/laravel booking-api
cd booking-api
```

## 2. Install Sanctum

Laravel 11/12 sudah menyertakan Sanctum untuk API. Jika belum, jalankan:

```bash
composer require laravel/sanctum
php artisan sanctum:install
```

Pastikan `config/sanctum.php` ada dan route `api.php` sudah dimuat lewat `bootstrap/app.php`
(Laravel 11+ memuatnya otomatis lewat `->withRouting(api: __DIR__.'/../routes/api.php')`).

## 3. Copy overlay dari paket ini

Timpa/copy folder berikut dari paket ini ke project Laravel:

```bash
cp -r app/Http/Controllers/Api   <project-laravel>/app/Http/Controllers/
cp    app/Http/Middleware/EnsureRole.php <project-laravel>/app/Http/Middleware/
cp -r app/Http/Requests          <project-laravel>/app/Http/
cp -r app/Models                 <project-laravel>/app/
cp    database/migrations/2026_08_01_*.php <project-laravel>/database/migrations/
cp    database/seeders/*.php     <project-laravel>/database/seeders/
cp    routes/api.php             <project-laravel>/routes/
```

> `app/Models/User.php` overlay **menggantikan** bawaan Laravel (tambah `HasApiTokens`
> dan kolom `role` di `$fillable`).

## 4. Daftarkan middleware alias `role`

Edit `bootstrap/app.php`, tambahkan `->withMiddleware(...)` persis seperti ini:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

Middleware `role:admin` akan mengembalikan **403** `{"message":"Akses khusus admin."}`
jika role user tidak cocok.

## 5. Konfigurasi database MySQL di `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_db
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost:8000
# FRONTEND_URL=http://localhost:5173  ← diisi bila perlu (dipakai config/cors.php)
```

Buat database-nya dulu:

```bash
mysql -u root -e "CREATE DATABASE booking_db;"
```

## 6. Migrasi + seed

```bash
php artisan migrate --seed
```

Seeder membuat akun default dan 6 layanan + jadwal (tanggal H+1..H+7 dari hari ini):

| email | password | role |
|---|---|---|
| admin@booking.test | password | admin |
| customer@booking.test | password | customer |

## 7. Jalankan server

```bash
php artisan serve
# API aktif di http://localhost:8000/api
```

**CORS:** `config/cors.php` bawaan Laravel sudah mengizinkan path `api/*` dengan
`supports_credentials`; cukup untuk frontend Vite di `http://localhost:5173`.
Jika origin frontend berbeda, set `FRONTEND_URL` di `.env`.

## 8. Uji cepat via curl

```bash
# Login admin
curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@booking.test","password":"password"}'
# → simpan nilai "token" dari response

# Daftar layanan (publik)
curl -s "http://localhost:8000/api/services?active=1"

# Jadwal sebuah layanan per tanggal (publik)
curl -s "http://localhost:8000/api/services/1/schedules?date=2026-08-02"

# Contoh endpoint admin (ganti <TOKEN>)
curl -s http://localhost:8000/api/reports/summary \
  -H "Authorization: Bearer <TOKEN>"
```
