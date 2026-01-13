# 🚀 Panduan Deploy Laravel ke cPanel Domainesia

## Persiapan di Komputer Lokal

### 1. Build Assets
```bash
npm install
npm run build
```

### 2. Optimize Composer
```bash
composer install --optimize-autoloader --no-dev
```

### 3. Buat file ZIP untuk upload
Zip semua file KECUALI:
- `node_modules/`
- `.git/`
- `tests/`
- `.env` (buat baru di server)

---

## Langkah di cPanel Domainesia

### Step 1: Buat Database MySQL
1. Login cPanel → **MySQL® Databases**
2. Create Database: `skpkuautara_db`
3. Create User: `skpkuautara_user` + password
4. Add User to Database → **ALL PRIVILEGES**

> Nama lengkap database: `cpanelusername_skpkuautara_db`

---

### Step 2: Upload dan Extract File

**Struktur yang dibutuhkan:**
```
/home/cpanelusername/
├── skp-app/                    ← Folder Laravel (buat folder baru)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── artisan
│   ├── composer.json
│   └── .env
│
└── public_html/                ← Isi dari folder "public/"
    ├── index.php              ← WAJIB EDIT
    ├── .htaccess
    ├── build/
    ├── images/
    └── robots.txt
```

**Langkah Upload:**
1. Upload ZIP ke `/home/cpanelusername/`
2. Extract
3. Rename folder hasil extract menjadi `skp-app`
4. Pindahkan ISI folder `public/` ke `public_html/`
5. Edit `public_html/index.php`

---

### Step 3: Edit index.php di public_html

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ubah path mengarah ke folder skp-app
require __DIR__.'/../skp-app/vendor/autoload.php';

$app = require_once __DIR__.'/../skp-app/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

---

### Step 4: Konfigurasi .env

Buat file `.env` di folder `skp-app/`:

```env
APP_NAME="SKP KUA Banjarmasin Utara"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://namadomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpanelusername_skpkuautara_db
DB_USERNAME=cpanelusername_skpkuautara_user
DB_PASSWORD=password_anda_disini

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=mail.namadomain.com
MAIL_PORT=465
MAIL_USERNAME=noreply@namadomain.com
MAIL_PASSWORD=password_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@namadomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

### Step 5: Set Permission

Via File Manager → klik kanan folder → Change Permissions:

| Folder | Permission |
|--------|------------|
| `skp-app/storage/` | 775 |
| `skp-app/storage/logs/` | 775 |
| `skp-app/storage/framework/` | 775 |
| `skp-app/storage/framework/cache/` | 775 |
| `skp-app/storage/framework/sessions/` | 775 |
| `skp-app/storage/framework/views/` | 775 |
| `skp-app/bootstrap/cache/` | 775 |

---

### Step 6: Generate App Key & Migrate

**Via Terminal cPanel:**
```bash
cd ~/skp-app
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Jika tidak ada Terminal, gunakan route sementara:**

Tambahkan di `routes/web.php`:
```php
// HAPUS SETELAH DIGUNAKAN!
Route::get('/setup-xyz123', function() {
    try {
        // Generate key jika belum ada
        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }
        
        // Migrate
        Artisan::call('migrate', ['--force' => true]);
        
        // Seed
        Artisan::call('db:seed', [
            '--class' => 'StaffKuaBanjarmasinUtaraSeeder',
            '--force' => true
        ]);
        
        // Cache
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        
        return 'Setup completed successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});
```

Akses: `https://namadomain.com/setup-xyz123`

⚠️ **PENTING: HAPUS ROUTE INI SETELAH SELESAI!**

---

### Step 7: Setup SSL (HTTPS)

1. Di cPanel → **SSL/TLS Status** atau **Let's Encrypt™ SSL**
2. Issue certificate untuk domain
3. Pastikan APP_URL di .env menggunakan `https://`

---

## Troubleshooting

### Error 500
```bash
# Cek error log
tail -f ~/skp-app/storage/logs/laravel.log

# Fix permission
chmod -R 775 ~/skp-app/storage
chmod -R 775 ~/skp-app/bootstrap/cache
```

### Blank Page
- Cek `APP_DEBUG=true` sementara untuk lihat error
- Cek file `.htaccess` ada di `public_html`

### Database Error
- Pastikan nama database diawali username cPanel
- Test koneksi via phpMyAdmin

### Assets Tidak Muncul
- Pastikan folder `build/` sudah dicopy ke `public_html/`
- Cek path di `public_html/build/manifest.json`

---

## Update Aplikasi

Untuk update di kemudian hari:

1. Build assets di lokal: `npm run build`
2. Upload file yang berubah via FTP
3. Via Terminal/SSH:
```bash
cd ~/skp-app
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Kontak Support Domainesia

Jika mengalami masalah teknis hosting:
- Live Chat: https://www.domainesia.com
- Ticket: Via member area
- WhatsApp: Cek website Domainesia

---

**Selamat! Aplikasi SKP KUA Banjarmasin Utara sudah live! 🎉**
