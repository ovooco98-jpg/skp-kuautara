# 🚀 Setup Laravel ke Vercel (Forced Mode)

*Warning: Vercel tidak ideal untuk Laravel, tapi kalau mau paksa, ini caranya!* ⚠️

## ⚠️ Limitations yang Harus Kamu Tahu

1. **No Persistent Storage** - Vercel adalah serverless, jadi tidak ada persistent file storage
2. **No SQLite** - Harus pakai external database (MySQL/PostgreSQL)
3. **Cold Starts** - Ada delay saat function pertama kali dipanggil
4. **File Upload** - Harus pakai external storage (S3, Cloudinary, dll)
5. **Queue Jobs** - Tidak bisa pakai Laravel Queue (kecuali pakai external service)
6. **Scheduled Tasks** - Tidak bisa pakai Laravel Scheduler (perlu external cron)

## 📋 Prerequisites

- ✅ Akun Vercel (gratis)
- ✅ External Database (MySQL/PostgreSQL) - **WAJIB!**
- ✅ External Storage untuk file upload (opsional, karena app ini pakai link drive)
- ✅ GitHub repository (untuk auto-deploy)

## 🗄️ Step 1: Setup External Database

Vercel tidak support SQLite, jadi kamu **WAJIB** pakai external database.

### Opsi A: PlanetScale (Recommended - Gratis Permanen) ⭐

1. Daftar di [PlanetScale](https://planetscale.com)
2. Buat database baru
3. Copy connection string
4. Format: `mysql://username:password@host:port/database?sslmode=require`

### Opsi B: Railway PostgreSQL (Gratis dengan Limit)

1. Daftar di [Railway](https://railway.app)
2. Create New Project → Add PostgreSQL
3. Copy connection string

### Opsi C: Supabase (Gratis dengan Limit)

1. Daftar di [Supabase](https://supabase.com)
2. Create New Project
3. Copy connection string dari Settings → Database

## 🔧 Step 2: Update Konfigurasi

### 2.1 Update `vercel.json`

File `vercel.json` sudah di-update dengan konfigurasi yang lebih lengkap. Cek apakah sudah sesuai.

### 2.2 Update Environment Variables

Di Vercel dashboard, tambahkan environment variables berikut:

```env
# App Configuration
APP_NAME="LKH KUA"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-project.vercel.app

# Database (WAJIB - pilih salah satu)
# Untuk MySQL (PlanetScale)
DB_CONNECTION=mysql
DB_HOST=your-host.planetscale.com
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

# Atau untuk PostgreSQL
# DB_CONNECTION=pgsql
# DB_HOST=your-host.supabase.co
# DB_PORT=5432
# DB_DATABASE=postgres
# DB_USERNAME=postgres
# DB_PASSWORD=your-password

# Session & Cache (pakai database karena serverless)
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail Configuration (untuk notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# Filesystem (opsional, kalau perlu file upload)
# Pakai S3 atau Cloudinary
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=your-bucket
AWS_URL=https://your-bucket.s3.amazonaws.com
```

## 🚀 Step 3: Deploy ke Vercel

### 3.1 Via Vercel CLI

```bash
# Install Vercel CLI
npm i -g vercel

# Login
vercel login

# Deploy
vercel

# Follow prompts:
# - Set up and deploy? Yes
# - Which scope? (pilih akun kamu)
# - Link to existing project? No
# - Project name? lkh-kua
# - Directory? ./
# - Override settings? No
```

### 3.2 Via GitHub (Recommended)

1. Push code ke GitHub
2. Login ke [Vercel Dashboard](https://vercel.com/dashboard)
3. Click **Add New Project**
4. Import repository dari GitHub
5. Vercel akan auto-detect Laravel
6. **JANGAN** ubah framework preset (biarkan blank)
7. Configure:
   - **Build Command**: `composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache`
   - **Output Directory**: `public`
   - **Install Command**: `composer install --no-dev --optimize-autoloader && npm ci`
8. Add semua environment variables
9. Click **Deploy**

## 🗃️ Step 4: Setup Database

Setelah deploy pertama kali, kamu perlu run migrations:

### Via Vercel CLI

```bash
# SSH ke function (tidak bisa langsung)
# Alternatif: pakai Vercel Functions logs
```

### Via Tinker (Temporary)

1. Buat file `vercel-migrate.php` di root:

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Run migrations
Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();

// Seed data (optional)
// Artisan::call('db:seed', ['--class' => 'StaffKuaBanjarmasinUtaraSeeder', '--force' => true]);
// echo Artisan::output();
```

2. Deploy file ini
3. Akses via browser: `https://your-project.vercel.app/vercel-migrate.php`
4. **HAPUS file ini setelah selesai!** (security risk)

### Via Database Tool (Recommended)

1. Install database client (TablePlus, DBeaver, atau phpMyAdmin)
2. Connect ke database external kamu
3. Run SQL migrations manual atau import schema

## 🔑 Step 5: Generate APP_KEY

```bash
# Generate key lokal
php artisan key:generate --show

# Copy key yang dihasilkan
# Paste ke Vercel environment variable: APP_KEY
```

Atau langsung set di Vercel:

```bash
# Via Vercel CLI
vercel env add APP_KEY
# Paste: base64:generated-key-here
```

## 📦 Step 6: Build Assets

Assets akan otomatis di-build saat deploy karena sudah ada di `buildCommand`. Tapi kalau perlu manual:

```bash
npm run build
```

## ✅ Step 7: Verify Deployment

1. Akses URL Vercel kamu
2. Cek apakah aplikasi load dengan benar
3. Test login
4. Test create LKH
5. Cek logs di Vercel dashboard jika ada error

## 🐛 Troubleshooting

### Error: "Class not found" atau "Autoload error"

**Solusi:**
- Pastikan `composer install` di-build command
- Cek apakah `vendor/` folder ter-upload (tidak di `.vercelignore`)

### Error: "Database connection failed"

**Solusi:**
- Pastikan environment variables database sudah benar
- Cek apakah database external accessible dari internet
- Untuk PlanetScale, pastikan SSL mode enabled

### Error: "Storage link not found"

**Solusi:**
- Vercel tidak support `php artisan storage:link`
- Pakai external storage (S3) atau serve files via CDN
- Atau ubah ke link drive (sudah diimplementasi di app ini)

### Error: "Session driver not working"

**Solusi:**
- Pastikan `SESSION_DRIVER=database` di env
- Pastikan table `sessions` sudah dibuat (via migration)
- Cek apakah cache table juga sudah dibuat

### Error: "Route not found" atau 404

**Solusi:**
- Pastikan `php artisan route:cache` di build command
- Cek `vercel.json` routes configuration
- Pastikan semua routes di-route ke `public/index.php`

### Cold Start Delay

**Solusi:**
- Ini normal untuk serverless
- Pertimbangkan pakai Vercel Pro untuk better performance
- Atau pakai platform lain yang lebih cocok untuk Laravel (Render, Fly.io)

## 🔄 Auto-Deploy dari GitHub

Setelah setup pertama, setiap push ke main branch akan auto-deploy:

1. Push code ke GitHub
2. Vercel akan auto-detect changes
3. Run build command
4. Deploy ke production

## 📊 Monitoring

- **Logs**: Vercel Dashboard → Your Project → Logs
- **Analytics**: Vercel Dashboard → Analytics
- **Functions**: Vercel Dashboard → Functions (lihat cold start times)

## 💡 Tips & Best Practices

1. **Minimize Cold Starts**: 
   - Pakai Vercel Pro (ada warm functions)
   - Optimize autoloader dengan `--optimize-autoloader`

2. **Database Connection**:
   - Pakai connection pooling (PlanetScale sudah include)
   - Set timeout yang reasonable

3. **Cache Everything**:
   - Config cache: `php artisan config:cache`
   - Route cache: `php artisan route:cache`
   - View cache: `php artisan view:cache`

4. **Environment Variables**:
   - Jangan commit `.env` ke Git
   - Set semua di Vercel dashboard
   - Gunakan Vercel CLI untuk bulk import

5. **File Storage**:
   - Pakai external storage (S3, Cloudinary)
   - Atau pakai link drive (sudah diimplementasi)

## 🚨 Known Issues

1. **Queue Jobs**: Tidak bisa pakai Laravel Queue di Vercel. Solusi:
   - Pakai external queue service (Redis Queue, SQS)
   - Atau disable queue, pakai sync

2. **Scheduled Tasks**: Tidak bisa pakai Laravel Scheduler. Solusi:
   - Pakai external cron service (Cron-job.org, EasyCron)
   - Atau trigger via API

3. **File Upload**: Tidak bisa pakai local storage. Solusi:
   - Pakai S3 atau Cloudinary
   - Atau pakai link drive (sudah diimplementasi di app ini)

## 📚 Alternative Platforms (Lebih Cocok untuk Laravel)

Kalau Vercel terlalu ribet, pertimbangkan:

- **Render** ⭐⭐⭐⭐⭐ - Free tier bagus, support Laravel dengan baik
- **Fly.io** ⭐⭐⭐⭐⭐ - Free tier bagus, global edge network
- **Railway** ⭐⭐⭐⭐ - Simple setup, Laravel + MySQL di satu platform

Lihat [HOSTING_ALTERNATIVES.md](HOSTING_ALTERNATIVES.md) untuk detail lengkap.

## 🎯 Quick Checklist

- [ ] External database setup (MySQL/PostgreSQL)
- [ ] Environment variables di Vercel
- [ ] APP_KEY generated dan di-set
- [ ] Build command configured
- [ ] Routes configured di vercel.json
- [ ] Database migrations run
- [ ] Seed data (optional)
- [ ] Test deployment
- [ ] Monitor logs untuk errors

---

**Good luck! Semoga berhasil paksa Laravel ke Vercel!** 🚀

*Kalau masih error, mungkin memang Vercel bukan tempat yang tepat untuk Laravel. Pertimbangkan pakai Render atau Fly.io yang lebih cocok.* 😅
