# 📚 Tutorial Lengkap: Deploy Laravel ke Render

*Panduan step-by-step dengan screenshot descriptions untuk deploy aplikasi LKH KUA ke Render* 🎯

---

## 📋 Daftar Isi

1. [Persiapan](#1-persiapan)
2. [Setup Database](#2-setup-database)
3. [Setup Render Account](#3-setup-render-account)
4. [Deploy Aplikasi](#4-deploy-aplikasi)
5. [Konfigurasi Environment Variables](#5-konfigurasi-environment-variables)
6. [Setup Database di Render](#6-setup-database-di-render)
7. [Generate APP_KEY](#7-generate-app_key)
8. [Run Migrations](#8-run-migrations)
9. [Testing & Verifikasi](#9-testing--verifikasi)
10. [Troubleshooting](#10-troubleshooting)

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
   git commit -m "Ready for Render deployment"
   git push origin main
   ```

**✅ Checklist:**
- [ ] Code sudah di GitHub
- [ ] Semua file penting sudah di-commit
- [ ] Branch `main` atau `master` sudah up-to-date

---

## 2. Setup Database

Kamu perlu database external karena Render tidak support SQLite. Pilih salah satu:

### Opsi A: PlanetScale MySQL ⭐ (Recommended)

**Kenapa PlanetScale?**
- ✅ Gratis permanen (free tier cukup untuk production)
- ✅ Serverless MySQL (auto-scaling)
- ✅ SSL enabled by default
- ✅ Connection pooling included

**Langkah-langkah:**

#### Step 1: Daftar PlanetScale
1. Buka browser, kunjungi: https://planetscale.com
2. Click **Sign Up** (atau **Sign In** kalau sudah punya akun)
3. Pilih sign up dengan GitHub (recommended) atau email
4. Verifikasi email jika perlu

#### Step 2: Create Database
1. Setelah login, kamu akan masuk ke Dashboard
2. Click tombol **"Create database"** atau **"New database"**
3. Isi form:
   - **Database name**: `lkh-kua` (atau nama lain)
   - **Region**: Pilih yang terdekat (Singapore/Asia Pacific)
   - **Plan**: Pilih **Free** (Hobby plan)
4. Click **"Create database"**
5. Tunggu beberapa detik sampai database siap

#### Step 3: Get Connection Details
1. Setelah database dibuat, kamu akan masuk ke database dashboard
2. Click tab **"Connect"** atau **"Connection strings"**
3. Pilih **"General"** connection (bukan Branch)
4. Copy informasi berikut (simpan untuk nanti):
   - **Host**: `xxx.psdb.cloud`
   - **Username**: `xxx`
   - **Password**: `xxx` (click **"Show password"** untuk lihat)
   - **Database name**: `lkh-kua` (atau nama yang kamu pilih)
   - **Port**: `3306`

**💡 Tips:** 
- Password hanya muncul sekali, jadi pastikan sudah di-copy!
- Simpan di tempat aman (password manager recommended)

#### Step 4: Create Branch (Optional)
PlanetScale pakai branching system seperti Git:
- **Main branch**: untuk production
- **Development branch**: untuk testing

Untuk sekarang, pakai main branch saja.

**✅ Checklist:**
- [ ] Akun PlanetScale sudah dibuat
- [ ] Database sudah dibuat
- [ ] Connection details sudah di-copy dan disimpan

---

### Opsi B: Render PostgreSQL (90 Hari Gratis)

**Langkah-langkah:**

#### Step 1: Daftar Render
1. Buka browser, kunjungi: https://render.com
2. Click **"Get Started for Free"** atau **"Sign Up"**
3. Sign up dengan GitHub (recommended) atau email
4. Verifikasi email jika perlu

#### Step 2: Create PostgreSQL Database
1. Setelah login, kamu akan masuk ke Dashboard
2. Click **"New +"** → **"PostgreSQL"**
3. Isi form:
   - **Name**: `lkh-kua-db`
   - **Database**: `lkh_kua` (atau nama lain)
   - **User**: `lkh_kua_user` (atau nama lain)
   - **Region**: **Singapore** (terdekat dengan Indonesia)
   - **Plan**: **Free** (90 hari gratis)
4. Click **"Create database"**
5. Tunggu beberapa menit sampai database siap

#### Step 3: Get Connection Details
1. Setelah database dibuat, click database name untuk masuk ke detail
2. Di bagian **"Connections"**, kamu akan lihat:
   - **Internal Database URL**: untuk koneksi dari Render service lain
   - **External Database URL**: untuk koneksi dari luar Render
3. Copy **Internal Database URL** (akan dipakai nanti)

**Format connection string:**
```
postgresql://user:password@host:port/database
```

**✅ Checklist:**
- [ ] Akun Render sudah dibuat
- [ ] PostgreSQL database sudah dibuat
- [ ] Connection string sudah di-copy

---

## 3. Setup Render Account

### Step 1: Daftar/Login ke Render

1. Buka browser, kunjungi: https://render.com
2. Jika belum punya akun:
   - Click **"Get Started for Free"**
   - Sign up dengan GitHub (recommended) atau email
   - Verifikasi email jika perlu
3. Jika sudah punya akun:
   - Click **"Sign In"**
   - Login dengan GitHub atau email

### Step 2: Connect GitHub (Jika Belum)

1. Setelah login, kamu akan masuk ke Dashboard
2. Jika belum connect GitHub:
   - Click **"New +"** → **"Web Service"**
   - Render akan minta akses GitHub
   - Authorize Render untuk akses repository
   - Pilih repository yang mau di-deploy (bisa pilih semua atau specific)

**✅ Checklist:**
- [ ] Akun Render sudah dibuat/login
- [ ] GitHub sudah di-connect ke Render
- [ ] Repository `lkh-kua` sudah accessible dari Render

---

## 4. Deploy Aplikasi

### Step 1: Create Web Service

1. Di Render Dashboard, click **"New +"** (tombol biru di kanan atas)
2. Pilih **"Web Service"**
3. Render akan minta connect GitHub (kalau belum):
   - Click **"Connect account"** atau **"Configure account"**
   - Authorize Render
   - Pilih repository yang mau di-deploy

### Step 2: Select Repository

1. Setelah GitHub connected, kamu akan lihat list repository
2. Pilih repository **`lkh-kua`** (atau nama repository kamu)
3. Click **"Connect"**

### Step 3: Configure Service

Render akan auto-detect `render.yaml`, tapi kita akan verifikasi:

**Basic Settings:**
- **Name**: `lkh-kua` (atau nama lain)
- **Environment**: **PHP** (Render akan auto-detect)
- **Region**: **Singapore** (terdekat dengan Indonesia)
- **Branch**: `main` (atau branch yang kamu pakai)
- **Plan**: **Free** (untuk mulai)

**Build & Deploy:**
- **Build Command**: 
  ```
  composer install --optimize-autoloader --no-dev && npm ci && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache
  ```
  *(Ini sudah ada di render.yaml, jadi akan auto-fill)*

- **Start Command**: 
  ```
  php artisan serve --host=0.0.0.0 --port=$PORT
  ```
  *(Ini juga sudah ada di render.yaml)*

**Advanced Settings:**
- **Health Check Path**: `/up` (sudah ada di render.yaml)
- **Auto-Deploy**: **Yes** (auto-deploy setiap push ke main)

### Step 4: Deploy

1. Scroll ke bawah, click **"Create Web Service"**
2. Render akan mulai build dan deploy
3. Tunggu beberapa menit (biasanya 5-10 menit untuk pertama kali)
4. Kamu bisa lihat progress di dashboard:
   - **Building**: Install dependencies, build assets
   - **Deploying**: Start service
   - **Live**: Aplikasi sudah running

**⚠️ Note:** 
- Deploy pertama kali mungkin gagal karena belum ada environment variables
- It's okay, kita akan setup environment variables dulu

**✅ Checklist:**
- [ ] Web service sudah dibuat
- [ ] Build sudah selesai (atau sedang running)
- [ ] URL aplikasi sudah muncul (format: `https://lkh-kua.onrender.com`)

---

## 5. Konfigurasi Environment Variables

### Step 1: Buka Environment Variables

1. Di Render Dashboard, click service **`lkh-kua`**
2. Di sidebar kiri, click **"Environment"**
3. Kamu akan lihat list environment variables (masih kosong atau ada default)

### Step 2: Add Environment Variables

Click **"Add Environment Variable"** untuk setiap variable berikut:

#### 5.1 App Configuration

**APP_NAME**
- **Key**: `APP_NAME`
- **Value**: `LKH KUA`
- Click **"Save Changes"**

**APP_ENV**
- **Key**: `APP_ENV`
- **Value**: `production`
- Click **"Save Changes"`

**APP_DEBUG**
- **Key**: `APP_DEBUG`
- **Value**: `false`
- Click **"Save Changes"`

**APP_URL**
- **Key**: `APP_URL`
- **Value**: `https://lkh-kua.onrender.com` (ganti dengan URL Render kamu)
- Untuk cek URL kamu: Dashboard → Service → bagian atas ada URL
- Click **"Save Changes"**

**APP_TIMEZONE**
- **Key**: `APP_TIMEZONE`
- **Value**: `Asia/Jakarta`
- Click **"Save Changes"**

**APP_LOCALE**
- **Key**: `APP_LOCALE`
- **Value**: `id`
- Click **"Save Changes"**

#### 5.2 Database Configuration

**Untuk PlanetScale MySQL:**

**DB_CONNECTION**
- **Key**: `DB_CONNECTION`
- **Value**: `mysql`
- Click **"Save Changes"**

**DB_HOST**
- **Key**: `DB_HOST`
- **Value**: `xxx.psdb.cloud` (dari PlanetScale connection details)
- Click **"Save Changes"**

**DB_PORT**
- **Key**: `DB_PORT`
- **Value**: `3306`
- Click **"Save Changes"**

**DB_DATABASE**
- **Key**: `DB_DATABASE`
- **Value**: `lkh-kua` (atau nama database kamu di PlanetScale)
- Click **"Save Changes"**

**DB_USERNAME**
- **Key**: `DB_USERNAME`
- **Value**: `xxx` (dari PlanetScale connection details)
- Click **"Save Changes"**

**DB_PASSWORD**
- **Key**: `DB_PASSWORD`
- **Value**: `xxx` (dari PlanetScale connection details)
- Click **"Save Changes"**

**MYSQL_ATTR_SSL_CA**
- **Key**: `MYSQL_ATTR_SSL_CA`
- **Value**: `/etc/ssl/certs/ca-certificates.crt`
- Click **"Save Changes"**

**Untuk Render PostgreSQL:**

**DB_CONNECTION**
- **Key**: `DB_CONNECTION`
- **Value**: `pgsql`
- Click **"Save Changes"`

**DB_HOST**
- **Key**: `DB_HOST`
- **Value**: `xxx.onrender.com` (dari Render PostgreSQL connection)
- Click **"Save Changes"**

**DB_PORT**
- **Key**: `DB_PORT`
- **Value**: `5432`
- Click **"Save Changes"**

**DB_DATABASE**
- **Key**: `DB_DATABASE`
- **Value**: `lkh_kua` (dari Render PostgreSQL)
- Click **"Save Changes"**

**DB_USERNAME**
- **Key**: `DB_USERNAME`
- **Value**: `xxx` (dari Render PostgreSQL)
- Click **"Save Changes"**

**DB_PASSWORD**
- **Key**: `DB_PASSWORD`
- **Value**: `xxx` (dari Render PostgreSQL)
- Click **"Save Changes"**

#### 5.3 Session & Cache

**SESSION_DRIVER**
- **Key**: `SESSION_DRIVER`
- **Value**: `database`
- Click **"Save Changes"**

**SESSION_LIFETIME**
- **Key**: `SESSION_LIFETIME`
- **Value**: `120`
- Click **"Save Changes"**

**CACHE_STORE**
- **Key**: `CACHE_STORE`
- **Value**: `database`
- Click **"Save Changes"**

**QUEUE_CONNECTION**
- **Key**: `QUEUE_CONNECTION`
- **Value**: `database`
- Click **"Save Changes"**

#### 5.4 Mail Configuration (Opsional - untuk Notifications)

**MAIL_MAILER**
- **Key**: `MAIL_MAILER`
- **Value**: `smtp`
- Click **"Save Changes"**

**MAIL_HOST**
- **Key**: `MAIL_HOST`
- **Value**: `smtp.gmail.com`
- Click **"Save Changes"**

**MAIL_PORT**
- **Key**: `MAIL_PORT`
- **Value**: `587`
- Click **"Save Changes"**

**MAIL_USERNAME**
- **Key**: `MAIL_USERNAME`
- **Value**: `your-email@gmail.com` (ganti dengan email kamu)
- Click **"Save Changes"**

**MAIL_PASSWORD**
- **Key**: `MAIL_PASSWORD`
- **Value**: `your-app-password` (Gmail App Password, bukan password biasa!)
- Click **"Save Changes"**

**💡 Cara dapat Gmail App Password:**
1. Buka Google Account → Security
2. Enable 2-Step Verification (kalau belum)
3. App Passwords → Generate
4. Pilih app: "Mail"
5. Pilih device: "Other (Custom name)" → ketik "Render"
6. Copy password yang di-generate
7. Paste ke `MAIL_PASSWORD`

**MAIL_ENCRYPTION**
- **Key**: `MAIL_ENCRYPTION`
- **Value**: `tls`
- Click **"Save Changes"**

**MAIL_FROM_ADDRESS**
- **Key**: `MAIL_FROM_ADDRESS`
- **Value**: `your-email@gmail.com` (sama dengan MAIL_USERNAME)
- Click **"Save Changes"**

**MAIL_FROM_NAME**
- **Key**: `MAIL_FROM_NAME`
- **Value**: `LKH KUA`
- Click **"Save Changes"**

#### 5.5 APP_KEY (Akan di-generate nanti)

Untuk sekarang, skip dulu. Kita akan generate di step berikutnya.

**✅ Checklist:**
- [ ] Semua environment variables sudah di-set
- [ ] Database connection details sudah benar
- [ ] APP_KEY belum di-set (akan di-generate nanti)

---

## 6. Setup Database di Render

### Step 1: Buka Render Shell

1. Di Render Dashboard, click service **`lkh-kua`**
2. Di sidebar kiri, click **"Shell"**
3. Render akan buka terminal di browser
4. Tunggu sampai shell ready (akan muncul prompt `$`)

### Step 2: Generate APP_KEY

1. Di Render Shell, ketik:
   ```bash
   php artisan key:generate --show
   ```
2. Tekan Enter
3. Render akan generate APP_KEY dan tampilkan di console
4. Copy output (format: `base64:xxx...`)
5. Kembali ke **Environment** tab
6. Add environment variable:
   - **Key**: `APP_KEY`
   - **Value**: `base64:xxx...` (paste hasil generate)
   - Click **"Save Changes"**

### Step 3: Test Database Connection

1. Di Render Shell, ketik:
   ```bash
   php artisan tinker
   ```
2. Tekan Enter
3. Ketik:
   ```php
   DB::connection()->getPdo();
   ```
4. Tekan Enter
5. Jika berhasil, akan muncul info PDO connection
6. Ketik `exit` untuk keluar dari tinker

**⚠️ Jika error:**
- Cek environment variables database (host, username, password)
- Untuk PlanetScale: pastikan `MYSQL_ATTR_SSL_CA` sudah di-set
- Cek logs di Render Dashboard untuk detail error

**✅ Checklist:**
- [ ] APP_KEY sudah di-generate dan di-set
- [ ] Database connection berhasil
- [ ] Tidak ada error di logs

---

## 7. Run Migrations

### Step 1: Run Migrations

1. Di Render Shell, ketik:
   ```bash
   php artisan migrate --force
   ```
2. Tekan Enter
3. Tunggu sampai migrations selesai
4. Kamu akan lihat output seperti:
   ```
   Migrating: 2025_12_14_103304_add_kua_fields_to_users_table
   Migrated:  2025_12_14_103304_add_kua_fields_to_users_table (XX.XXms)
   ...
   ```

**✅ Checklist:**
- [ ] Migrations berhasil di-run
- [ ] Tidak ada error

### Step 2: Seed Data (Optional)

1. Di Render Shell, ketik:
   ```bash
   php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder --force
   ```
2. Tekan Enter
3. Tunggu sampai seeding selesai

**✅ Checklist:**
- [ ] Seed data berhasil (jika di-run)
- [ ] User staff sudah ada di database

### Step 3: Create Storage Link

1. Di Render Shell, ketik:
   ```bash
   php artisan storage:link
   ```
2. Tekan Enter
3. Output: `The [public/storage] link has been connected to [storage/app/public].`

**✅ Checklist:**
- [ ] Storage link sudah dibuat

### Step 4: Optimize (Opsional)

1. Di Render Shell, ketik:
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

1. Di Render Dashboard, click service **`lkh-kua`**
2. Di bagian atas, ada URL aplikasi (format: `https://lkh-kua.onrender.com`)
3. Click URL atau copy-paste ke browser
4. Aplikasi akan load (mungkin agak lambat pertama kali karena cold start)

### Step 2: Test Login

1. Di halaman login, coba login dengan user yang sudah di-seed
2. Jika belum ada user, buat user baru via Render Shell:
   ```bash
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

1. Di Render Dashboard, click service **`lkh-kua`**
2. Click tab **"Logs"**
3. Cek apakah ada error atau warning
4. Jika ada error, lihat detail dan fix sesuai

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
- Build gagal di Render
- Error di build logs

**Solusi:**
1. Check build logs di Render Dashboard → Logs
2. Common errors:
   - **Composer error**: Cek `composer.json`, pastikan dependencies valid
   - **NPM error**: Cek `package.json`, pastikan dependencies valid
   - **PHP version**: Pastikan PHP 8.2+ (Render auto-detect)
3. Fix error sesuai log
4. Redeploy (auto-deploy atau manual)

### Problem: Database Connection Failed

**Gejala:**
- Error: "SQLSTATE[HY000] [2002] Connection refused"
- Error: "Access denied for user"

**Solusi:**
1. Cek environment variables database:
   - Host, port, username, password sudah benar?
   - Untuk PlanetScale: pastikan `MYSQL_ATTR_SSL_CA` sudah di-set
2. Test connection via Render Shell:
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```
3. Cek database external:
   - PlanetScale: Pastikan database active
   - Render PostgreSQL: Pastikan database running

### Problem: APP_KEY Error

**Gejala:**
- Error: "No application encryption key has been specified"

**Solusi:**
1. Generate APP_KEY via Render Shell:
   ```bash
   php artisan key:generate --show
   ```
2. Copy output
3. Set di Environment Variables:
   - Key: `APP_KEY`
   - Value: `base64:xxx...`
4. Redeploy service

### Problem: Service Sleep (Free Tier)

**Gejala:**
- Aplikasi lambat saat pertama kali diakses
- Service "sleep" setelah 15 menit tidak aktif

**Solusi:**
- **Ini normal untuk free tier**
- Pertimbangkan:
  - Upgrade ke paid plan ($7/bulan) untuk always-on
  - Atau setup external cron untuk ping service setiap 10 menit:
    - [Cron-job.org](https://cron-job.org) - gratis
    - Setup HTTP request ke `https://your-app.onrender.com/up`

### Problem: 500 Error

**Gejala:**
- Aplikasi return 500 Internal Server Error

**Solusi:**
1. Check logs di Render Dashboard → Logs
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
   - Render Dashboard → Logs → Build logs
3. Clear cache:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

---

## 10. Next Steps

### Setup Custom Domain (Opsional)

1. Di Render Dashboard → Service → Settings
2. Scroll ke **"Custom Domains"**
3. Add domain: `app.yourdomain.com`
4. Update DNS records sesuai instruksi Render
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
   - PlanetScale: Auto-backups included
   - Render PostgreSQL: Manual backup atau upgrade untuk auto-backups

3. **Monitor Usage:**
   - Check Render Dashboard untuk resource usage
   - Monitor free tier limits

---

## ✅ Final Checklist

- [ ] Code sudah di GitHub
- [ ] Database external sudah setup (PlanetScale atau Render PostgreSQL)
- [ ] Render account sudah dibuat
- [ ] Web service sudah dibuat dan deployed
- [ ] Environment variables sudah di-set
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

Aplikasi Laravel kamu sekarang sudah live di Render! 🚀

**URL Aplikasi:** `https://lkh-kua.onrender.com` (atau URL kamu)

**Tips:**
- Setiap push ke `main` branch akan auto-deploy
- Monitor logs secara berkala
- Keep dependencies updated
- Enjoy your deployed app! 🎊

---

## 📚 Additional Resources

- [Render Documentation](https://render.com/docs)
- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [PlanetScale Documentation](https://planetscale.com/docs)
- [SETUP_RENDER.md](SETUP_RENDER.md) - Quick reference
- [SETUP_PLANETSCALE.md](SETUP_PLANETSCALE.md) - Setup MySQL gratis

---

**Need Help?**
- Check documentation di atas
- Check Render logs untuk errors
- Open issue di GitHub repository

*Happy deploying!* ✨
