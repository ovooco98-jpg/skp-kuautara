# 🚂 Tutorial Lengkap: Deploy Laravel ke Railway

*Panduan step-by-step untuk deploy aplikasi LKH KUA ke Railway dengan MySQL* 🎯

**Platform:** Railway (Laravel + MySQL di satu platform)  
**Database:** MySQL (gratis dengan Railway free tier)

---

## 📋 Daftar Isi

1. [Persiapan](#1-persiapan)
2. [Setup Railway Account](#2-setup-railway-account)
3. [Deploy Aplikasi](#3-deploy-aplikasi)
4. [Setup MySQL Database](#4-setup-mysql-database)
5. [Konfigurasi Environment Variables](#5-konfigurasi-environment-variables)
6. [Generate APP_KEY](#6-generate-app_key)
7. [Run Migrations](#7-run-migrations)
8. [Testing & Verifikasi](#8-testing--verifikasi)
9. [Troubleshooting](#9-troubleshooting)

---

## 1. Persiapan

### 1.1 Pastikan Code Sudah di GitHub

**Langkah:**
1. Buka terminal/command prompt
2. Navigate ke folder project:
   ```bash
   cd c:\Users\Administrator\lkh-kua
   ```
3. Check status Git:
   ```bash
   git status
   ```
4. Jika ada perubahan, commit dan push:
   ```bash
   git add .
   git commit -m "Ready for Railway deployment"
   git push origin main
   ```

**✅ Checklist:**
- [ ] Code sudah di GitHub
- [ ] Semua file penting sudah di-commit
- [ ] Branch `main` atau `master` sudah up-to-date

---

## 2. Setup Railway Account

### Step 1: Daftar Railway

1. Buka browser, kunjungi: https://railway.app
2. Click **"Start a New Project"** atau **"Login"**
3. Pilih **"Login with GitHub"** (recommended)
4. Authorize Railway untuk akses GitHub account
5. Setelah login, kamu akan masuk ke Railway Dashboard

**💡 Tips:**
- Railway free tier memberikan $5 credit gratis untuk testing
- Setelah credit habis, bisa upgrade ke paid plan ($5/bulan)

**✅ Checklist:**
- [ ] Akun Railway sudah dibuat
- [ ] GitHub sudah di-connect ke Railway

---

## 3. Deploy Aplikasi

### Step 1: Create New Project

1. Di Railway Dashboard, click **"New Project"** (tombol biru)
2. Pilih **"Deploy from GitHub repo"**
3. Railway akan tampilkan list repository GitHub kamu
4. Pilih repository **`lkh-kua`** (atau nama repository kamu)
5. Click repository untuk deploy

### Step 2: Railway Auto-Detection

Railway akan otomatis:
- Detect bahwa ini Laravel project
- Setup build command (dari `railway.json` atau auto-detect)
- Setup start command
- Create service untuk aplikasi

**Tunggu beberapa menit** sampai Railway selesai build dan deploy.

### Step 3: Verify Deployment

1. Setelah build selesai, kamu akan lihat service **`lkh-kua`** di dashboard
2. Railway akan generate URL untuk aplikasi (format: `lkh-kua-production.up.railway.app`)
3. Click URL untuk akses aplikasi (mungkin masih error karena belum ada database)

**✅ Checklist:**
- [ ] Project sudah dibuat
- [ ] Service sudah di-deploy
- [ ] URL aplikasi sudah muncul

---

## 4. Setup MySQL Database

### Step 1: Add MySQL Database

1. Di Railway project dashboard, click **"New"** (tombol biru)
2. Pilih **"Database"** → **"Add MySQL"**
3. Railway akan otomatis create MySQL database
4. Tunggu beberapa detik sampai database siap

### Step 2: Get Connection Details

1. Setelah database dibuat, click database service (nama: `MySQL`)
2. Di tab **"Variables"**, kamu akan lihat environment variables:
   - `MYSQLHOST` - Host database
   - `MYSQLPORT` - Port (default: 3306)
   - `MYSQLDATABASE` - Nama database
   - `MYSQLUSER` - Username
   - `MYSQLPASSWORD` - Password
   - `MYSQL_URL` - Connection string lengkap

**💡 Tips:**
- Railway otomatis generate semua connection details
- Variables ini akan otomatis available untuk service lain di project yang sama
- Tidak perlu copy manual, Railway akan inject otomatis!

**✅ Checklist:**
- [ ] MySQL database sudah dibuat
- [ ] Connection variables sudah muncul di database service

---

## 5. Konfigurasi Environment Variables

### Step 1: Buka Environment Variables

1. Di Railway project dashboard, click service **`lkh-kua`** (web service)
2. Click tab **"Variables"**
3. Kamu akan lihat list environment variables (masih kosong atau ada default)

### Step 2: Quick Setup (Copy-Paste Ready)

**💡 Tips:** Untuk setup cepat, kamu bisa copy semua variables dari file:
- **[railway-env-variables.json](railway-env-variables.json)** - JSON format
- **[railway-env-variables-template.md](railway-env-variables-template.md)** - Template dengan penjelasan

Atau ikuti step-by-step di bawah ini untuk manual setup.

### Step 3: Add Environment Variables

Railway akan otomatis inject MySQL variables ke web service, tapi kita perlu set yang lain:

#### 5.1 App Configuration

**APP_NAME**
- Click **"New Variable"**
- **Key**: `APP_NAME`
- **Value**: `LKH KUA`
- Click **"Add"**

**APP_ENV**
- Click **"New Variable"**
- **Key**: `APP_ENV`
- **Value**: `production`
- Click **"Add"**

**APP_DEBUG**
- Click **"New Variable"**
- **Key**: `APP_DEBUG`
- **Value**: `false`
- Click **"Add"**

**APP_URL**
- Click **"New Variable"**
- **Key**: `APP_URL`
- **Value**: `https://lkh-kua-production.up.railway.app` (ganti dengan URL Railway kamu)
- Untuk cek URL: Dashboard → Service → tab **"Settings"** → **"Generate Domain"** atau lihat di **"Deployments"**
- Click **"Add"**

**APP_TIMEZONE**
- Click **"New Variable"**
- **Key**: `APP_TIMEZONE`
- **Value**: `Asia/Jakarta`
- Click **"Add"**

**APP_LOCALE**
- Click **"New Variable"**
- **Key**: `APP_LOCALE`
- **Value**: `id`
- Click **"Add"**

#### 5.2 Database Configuration

**DB_CONNECTION**
- Click **"New Variable"**
- **Key**: `DB_CONNECTION`
- **Value**: `mysql`
- Click **"Add"**

**DB_HOST**
- Click **"New Variable"**
- **Key**: `DB_HOST`
- **Value**: `${{MySQL.MYSQLHOST}}` (Railway akan auto-inject dari MySQL service)
- **Atau** bisa langsung reference: Click **"Reference Variable"** → Pilih MySQL service → Pilih `MYSQLHOST`
- Click **"Add"**

**DB_PORT**
- Click **"New Variable"**
- **Key**: `DB_PORT`
- **Value**: `${{MySQL.MYSQLPORT}}` (atau reference `MYSQLPORT`)
- Click **"Add"**

**DB_DATABASE**
- Click **"New Variable"**
- **Key**: `DB_DATABASE`
- **Value**: `${{MySQL.MYSQLDATABASE}}` (atau reference `MYSQLDATABASE`)
- Click **"Add"**

**DB_USERNAME**
- Click **"New Variable"**
- **Key**: `DB_USERNAME`
- **Value**: `${{MySQL.MYSQLUSER}}` (atau reference `MYSQLUSER`)
- Click **"Add"**

**DB_PASSWORD**
- Click **"New Variable"**
- **Key**: `DB_PASSWORD`
- **Value**: `${{MySQL.MYSQLPASSWORD}}` (atau reference `MYSQLPASSWORD`)
- Click **"Add"**

**💡 Tips Railway:**
- Railway support **variable references** dengan syntax `${{ServiceName.VariableName}}`
- Ini lebih aman dan otomatis update jika database credentials berubah
- Atau bisa pakai **"Reference Variable"** button untuk pilih dari dropdown

#### 5.3 Session & Cache

**SESSION_DRIVER**
- Click **"New Variable"**
- **Key**: `SESSION_DRIVER`
- **Value**: `database`
- Click **"Add"**

**SESSION_LIFETIME**
- Click **"New Variable"**
- **Key**: `SESSION_LIFETIME`
- **Value**: `120`
- Click **"Add"`

**CACHE_STORE**
- Click **"New Variable"**
- **Key**: `CACHE_STORE`
- **Value**: `database`
- Click **"Add"**

**QUEUE_CONNECTION**
- Click **"New Variable"**
- **Key**: `QUEUE_CONNECTION`
- **Value**: `database`
- Click **"Add"**

#### 5.4 Mail Configuration (Opsional - untuk Notifications)

**MAIL_MAILER**
- Click **"New Variable"**
- **Key**: `MAIL_MAILER`
- **Value**: `smtp`
- Click **"Add"**

**MAIL_HOST**
- Click **"New Variable"**
- **Key**: `MAIL_HOST`
- **Value**: `smtp.gmail.com`
- Click **"Add"**

**MAIL_PORT**
- Click **"New Variable"**
- **Key**: `MAIL_PORT`
- **Value**: `587`
- Click **"Add"**

**MAIL_USERNAME**
- Click **"New Variable"**
- **Key**: `MAIL_USERNAME`
- **Value**: `your-email@gmail.com` (ganti dengan email kamu)
- Click **"Add"**

**MAIL_PASSWORD**
- Click **"New Variable"**
- **Key**: `MAIL_PASSWORD`
- **Value**: `your-app-password` (Gmail App Password, bukan password biasa!)
- Click **"Add"**

**💡 Cara dapat Gmail App Password:**
1. Buka Google Account → Security
2. Enable 2-Step Verification (kalau belum)
3. App Passwords → Generate
4. Pilih app: "Mail"
5. Pilih device: "Other (Custom name)" → ketik "Railway"
6. Copy password yang di-generate
7. Paste ke `MAIL_PASSWORD`

**MAIL_ENCRYPTION**
- Click **"New Variable"**
- **Key**: `MAIL_ENCRYPTION`
- **Value**: `tls`
- Click **"Add"**

**MAIL_FROM_ADDRESS**
- Click **"New Variable"**
- **Key**: `MAIL_FROM_ADDRESS`
- **Value**: `your-email@gmail.com` (sama dengan MAIL_USERNAME)
- Click **"Add"**

**MAIL_FROM_NAME**
- Click **"New Variable"**
- **Key**: `MAIL_FROM_NAME`
- **Value**: `LKH KUA`
- Click **"Add"**

#### 5.5 APP_KEY (Akan di-generate nanti)

Untuk sekarang, skip dulu. Kita akan generate di step berikutnya.

**✅ Checklist:**
- [ ] Semua environment variables sudah di-set
- [ ] Database variables menggunakan reference ke MySQL service
- [ ] APP_KEY belum di-set (akan di-generate nanti)

---

## 6. Generate APP_KEY

### Step 1: Buka Railway CLI atau Web Terminal

Railway menyediakan 2 cara untuk akses terminal:

**Opsi A: Railway CLI (Recommended)**
1. Install Railway CLI:
   ```bash
   # Windows (PowerShell)
   iwr https://railway.app/install.ps1 | iex
   
   # Mac/Linux
   curl -fsSL https://railway.app/install.sh | sh
   ```
2. Login:
   ```bash
   railway login
   ```
3. Link project:
   ```bash
   railway link
   ```
4. Buka shell:
   ```bash
   railway shell
   ```

**Opsi B: Railway Web Terminal**
1. Di Railway Dashboard → Service `lkh-kua`
2. Click tab **"Deployments"**
3. Click deployment terbaru
4. Click **"View Logs"** → **"Shell"** (jika available)

**Opsi C: Railway Web Terminal (Alternative)**
1. Di Railway Dashboard → Service `lkh-kua`
2. Click tab **"Settings"**
3. Scroll ke **"Deploy"** section
4. Ada opsi untuk open terminal (jika available)

### Step 2: Generate APP_KEY

1. Setelah masuk ke terminal, ketik:
   ```bash
   php artisan key:generate --show
   ```
2. Tekan Enter
3. Railway akan generate APP_KEY dan tampilkan di console
4. Copy output (format: `base64:xxx...`)
5. Kembali ke Railway Dashboard → Service → **"Variables"**
6. Add environment variable:
   - Click **"New Variable"**
   - **Key**: `APP_KEY`
   - **Value**: `base64:xxx...` (paste hasil generate)
   - Click **"Add"**
7. Railway akan otomatis redeploy setelah add variable baru

**✅ Checklist:**
- [ ] APP_KEY sudah di-generate
- [ ] APP_KEY sudah di-set di environment variables
- [ ] Service sudah auto-redeploy

---

## 7. Run Migrations

### Step 1: Run Migrations

1. Buka Railway terminal (via CLI atau web)
2. Ketik:
   ```bash
   php artisan migrate --force
   ```
3. Tekan Enter
4. Tunggu sampai migrations selesai
5. Kamu akan lihat output seperti:
   ```
   Migrating: 2025_12_14_103304_add_kua_fields_to_users_table
   Migrated:  2025_12_14_103304_add_kua_fields_to_users_table (XX.XXms)
   ...
   ```

**✅ Checklist:**
- [ ] Migrations berhasil di-run
- [ ] Tidak ada error

### Step 2: Seed Data (Optional)

1. Di Railway terminal, ketik:
   ```bash
   php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder --force
   ```
2. Tekan Enter
3. Tunggu sampai seeding selesai

**✅ Checklist:**
- [ ] Seed data berhasil (jika di-run)
- [ ] User staff sudah ada di database

### Step 3: Create Storage Link

1. Di Railway terminal, ketik:
   ```bash
   php artisan storage:link
   ```
2. Tekan Enter
3. Output: `The [public/storage] link has been connected to [storage/app/public].`

**✅ Checklist:**
- [ ] Storage link sudah dibuat

### Step 4: Optimize (Opsional)

1. Di Railway terminal, ketik:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
2. Tekan Enter untuk setiap command

**Note:** Ini sudah otomatis di-build command, tapi tidak ada salahnya run lagi.

**✅ Checklist:**
- [ ] Semua optimizations sudah di-run
- [ ] Tidak ada error

---

## 8. Testing & Verifikasi

### Step 1: Akses Aplikasi

1. Di Railway Dashboard → Service `lkh-kua`
2. Di bagian atas, ada URL aplikasi (format: `https://lkh-kua-production.up.railway.app`)
3. Click URL atau copy-paste ke browser
4. Aplikasi akan load

### Step 2: Test Login

1. Di halaman login, coba login dengan user yang sudah di-seed
2. Jika belum ada user, buat user baru via Railway terminal:
   ```bash
   railway shell
   php artisan tinker
   ```
   ```php
   \App\Models\User::create([
       'name' => 'Admin',
       'email' => 'admin@example.com',
       'password' => bcrypt('password'),
       'nip' => '123456789',
       'role' => 'kepala_kua',
       'jabatan' => 'Kepala KUA',
       'unit_kerja' => 'KUA Kecamatan Banjarmasin Utara',
       'is_active' => true,
   ]);
   ```
   ```php
   exit
   ```

### Step 3: Test Features

1. **Test Dashboard:**
   - Login ke aplikasi
   - Cek apakah dashboard load dengan benar
   - Cek apakah statistik muncul

2. **Test Create LKH:**
   - Click "Buat LKH Baru"
   - Isi form
   - Submit
   - Cek apakah LKH tersimpan

3. **Test Approval (jika sebagai Kepala KUA):**
   - Submit LKH
   - Approve/Reject
   - Cek apakah status berubah

### Step 4: Check Logs

1. Di Railway Dashboard → Service `lkh-kua`
2. Click tab **"Deployments"**
3. Click deployment terbaru
4. Click **"View Logs"**
5. Cek apakah ada error atau warning
6. Jika ada error, lihat detail dan fix sesuai

**✅ Checklist:**
- [ ] Aplikasi bisa diakses
- [ ] Login berhasil
- [ ] Dashboard load dengan benar
- [ ] Create LKH berhasil
- [ ] Tidak ada error di logs

---

## 9. Troubleshooting

### Problem: Build Failed

**Gejala:**
- Build gagal di Railway
- Error di build logs

**Solusi:**
1. Check build logs di Railway Dashboard → Deployments → View Logs
2. Common errors:
   - **Composer error**: Cek `composer.json`, pastikan dependencies valid
   - **NPM error**: Cek `package.json`, pastikan dependencies valid
   - **PHP version**: Railway auto-detect PHP 8.2+, pastikan compatible
3. Fix error sesuai log
4. Railway akan auto-redeploy setelah fix

### Problem: Database Connection Failed

**Gejala:**
- Error: "SQLSTATE[HY000] [2002] Connection refused"
- Error: "Access denied for user"

**Solusi:**
1. Cek environment variables database:
   - Pastikan menggunakan **variable references** (`${{MySQL.MYSQLHOST}}`)
   - Atau pastikan values benar jika manual
2. Test connection via Railway terminal:
   ```bash
   railway shell
   php artisan tinker
   DB::connection()->getPdo();
   ```
3. Cek MySQL service:
   - Pastikan MySQL service running di Railway dashboard
   - Pastikan variables sudah di-reference dengan benar

### Problem: APP_KEY Error

**Gejala:**
- Error: "No application encryption key has been specified"

**Solusi:**
1. Generate APP_KEY via Railway terminal:
   ```bash
   railway shell
   php artisan key:generate --show
   ```
2. Copy output
3. Set di Environment Variables:
   - Key: `APP_KEY`
   - Value: `base64:xxx...`
4. Railway akan auto-redeploy

### Problem: 500 Error

**Gejala:**
- Aplikasi return 500 Internal Server Error

**Solusi:**
1. Check logs di Railway Dashboard → Deployments → View Logs
2. Common causes:
   - Missing environment variables
   - Database connection error
   - Missing migrations
   - Permission error
3. Fix sesuai error di logs

### Problem: Assets Not Loading

**Gejala:**
- CSS/JS tidak load
- Images tidak muncul

**Solusi:**
1. Pastikan build command include `npm run build`
2. Check apakah assets di-build dengan benar:
   - Railway Dashboard → Deployments → View Logs → Build logs
3. Clear cache:
   ```bash
   railway shell
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

### Problem: Service Not Starting

**Gejala:**
- Service tidak bisa start
- Crash loop

**Solusi:**
1. Check logs untuk error message
2. Common causes:
   - Wrong start command
   - Missing environment variables
   - Port conflict
3. Fix sesuai error
4. Railway akan auto-restart

### Problem: Free Tier Credit Habis

**Gejala:**
- Service stopped
- Error: "Insufficient credits"

**Solusi:**
- **Upgrade ke paid plan** ($5/bulan) untuk continue
- Atau pindah ke platform lain (Render, Fly.io) yang masih free

---

## 10. Next Steps

### Setup Custom Domain (Opsional)

1. Di Railway Dashboard → Service → Settings
2. Scroll ke **"Custom Domains"**
3. Add domain: `app.yourdomain.com`
4. Update DNS records sesuai instruksi Railway
5. SSL akan otomatis di-generate

### Setup Monitoring

1. **Uptime Monitoring:**
   - [UptimeRobot](https://uptimerobot.com) - gratis
   - Setup HTTP monitor untuk URL aplikasi
   - Get alert jika aplikasi down

2. **Error Tracking:**
   - Check logs secara berkala
   - Setup email notifications untuk errors (jika perlu)

### Regular Maintenance

1. **Update Dependencies:**
   ```bash
   composer update
   npm update
   ```
   Commit dan push untuk auto-deploy

2. **Database Backups:**
   - Railway tidak provide auto-backups di free tier
   - Manual backup via Railway terminal:
     ```bash
     railway shell
     mysqldump -u $MYSQLUSER -p$MYSQLPASSWORD -h $MYSQLHOST $MYSQLDATABASE > backup.sql
     ```

3. **Monitor Usage:**
   - Check Railway Dashboard untuk credit usage
   - Monitor free tier limits

---

## ✅ Final Checklist

- [ ] Code sudah di GitHub
- [ ] Railway account sudah dibuat
- [ ] Project sudah dibuat dan deployed
- [ ] MySQL database sudah dibuat
- [ ] Environment variables sudah di-set
- [ ] Database variables menggunakan references
- [ ] APP_KEY sudah di-generate
- [ ] Database connection berhasil
- [ ] Migrations sudah di-run
- [ ] Seed data sudah di-run (jika perlu)
- [ ] Storage link sudah dibuat
- [ ] Aplikasi bisa diakses
- [ ] Login berhasil
- [ ] Features berfungsi dengan benar
- [ ] Tidak ada error di logs

---

## 🎉 Selamat!

Aplikasi Laravel kamu sekarang sudah live di Railway! 🚂

**URL Aplikasi:** `https://lkh-kua-production.up.railway.app` (atau URL kamu)

**Tips:**
- Setiap push ke `main` branch akan auto-deploy
- Monitor logs secara berkala
- Keep dependencies updated
- Enjoy your deployed app! 🎊

---

## 📚 Additional Resources

- [Railway Documentation](https://docs.railway.app)
- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [Railway CLI Documentation](https://docs.railway.app/develop/cli)
- [railway.json](railway.json) - Railway configuration file
- [railway-env-variables.json](railway-env-variables.json) - **Copy-paste ready environment variables** 📋
- [railway-env-variables-template.md](railway-env-variables-template.md) - Template dengan penjelasan lengkap

---

## 💰 Pricing Info

**Free Tier:**
- $5 credit gratis untuk testing
- Setelah credit habis, service akan stop

**Paid Plan:**
- **Starter**: $5/bulan
  - $5 credit included
  - Unlimited deploys
  - Custom domains
  - Better support

**Note:** Jika free tier sudah habis, pertimbangkan pindah ke Render atau Fly.io yang masih free.

---

**Need Help?**
- Check [Railway Documentation](https://docs.railway.app)
- Check [Laravel Documentation](https://laravel.com/docs)
- Open issue di GitHub repository

*Happy deploying!* ✨
