# 🚀 Setup Laravel ke Render (Complete Guide)

*Panduan lengkap deploy Laravel app ke Render - the easy way!* ✨

## 🎯 Why Render?

- ✅ **750 jam/bulan gratis** (cukup untuk 1 service)
- ✅ **Auto-deploy dari GitHub** (set it and forget it)
- ✅ **Support Laravel dengan baik** (no hacks needed)
- ✅ **PostgreSQL gratis 90 hari** (atau pakai PlanetScale MySQL gratis permanen)
- ✅ **SSL gratis otomatis**
- ✅ **Environment variables management** yang mudah
- ✅ **Logs monitoring** real-time

---

## 📋 Prerequisites

- ✅ Akun GitHub (untuk repository)
- ✅ Akun Render (gratis) - [Daftar di sini](https://render.com)
- ✅ External Database (PlanetScale MySQL atau Render PostgreSQL)

---

## 🗄️ Step 1: Setup Database (Pilih Salah Satu)

### Opsi A: PlanetScale MySQL ⭐ (Recommended - Gratis Permanen)

**Kenapa PlanetScale?**
- ✅ Gratis permanen (free tier cukup untuk production)
- ✅ Serverless MySQL (auto-scaling)
- ✅ Connection pooling included
- ✅ Branching database (like Git branches!)
- ✅ SSL enabled by default

**Setup:**
1. Daftar di [PlanetScale](https://planetscale.com)
2. Create new database
3. Copy connection details:
   - Host: `xxx.psdb.cloud`
   - Username: `xxx`
   - Password: `xxx`
   - Database: `xxx`
   - Port: `3306`

**Detail lengkap:** Lihat [SETUP_PLANETSCALE.md](SETUP_PLANETSCALE.md)

### Opsi B: Render PostgreSQL (90 Hari Gratis)

**Setup:**
1. Di Render dashboard: **New → PostgreSQL**
2. Name: `lkh-kua-db`
3. Plan: **Free** (90 hari gratis, lalu $7/bulan)
4. Region: **Singapore** (sama dengan web service)
5. Copy connection string dari dashboard

---

## 🚀 Step 2: Deploy ke Render

### 2.1 Push Code ke GitHub

Pastikan code sudah di GitHub:
```bash
git add .
git commit -m "Ready for Render deployment"
git push origin main
```

### 2.2 Connect ke Render

1. Login ke [Render Dashboard](https://dashboard.render.com)
2. Click **New +** → **Web Service**
3. Connect GitHub account (kalau belum)
4. Pilih repository `lkh-kua`

### 2.3 Configure Service

Render akan auto-detect `render.yaml`, tapi kalau perlu manual:

**Basic Settings:**
- **Name**: `lkh-kua`
- **Environment**: `PHP`
- **Region**: `Singapore` (terdekat dengan Indonesia)
- **Branch**: `main` (atau branch yang kamu pakai)
- **Plan**: `Free`

**Build & Deploy:**
- **Build Command**: 
  ```bash
  composer install --optimize-autoloader --no-dev && npm ci && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache
  ```
- **Start Command**: 
  ```bash
  php artisan serve --host=0.0.0.0 --port=$PORT
  ```

**Advanced Settings:**
- **Health Check Path**: `/` (atau `/dashboard` kalau ada)
- **Auto-Deploy**: `Yes` (auto-deploy setiap push ke main)

---

## 🔧 Step 3: Setup Environment Variables

Di Render dashboard → Web Service → **Environment**, tambahkan:

### 3.1 App Configuration

```env
APP_NAME="LKH KUA"
APP_ENV=production
APP_KEY=  # Akan di-generate di step berikutnya
APP_DEBUG=false
APP_URL=https://lkh-kua.onrender.com  # Ganti dengan URL Render kamu
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
```

### 3.2 Database Configuration

**Untuk PlanetScale MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=xxx.psdb.cloud
DB_PORT=3306
DB_DATABASE=xxx
DB_USERNAME=xxx
DB_PASSWORD=xxx
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt
```

**Untuk Render PostgreSQL:**
```env
DB_CONNECTION=pgsql
DB_HOST=xxx.onrender.com
DB_PORT=5432
DB_DATABASE=xxx
DB_USERNAME=xxx
DB_PASSWORD=xxx
```

### 3.3 Session & Cache

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 3.4 Mail Configuration (untuk Notifications)

**Gmail SMTP:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password  # App Password, bukan password Gmail biasa!
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Cara dapat Gmail App Password:**
1. Google Account → Security
2. Enable 2-Step Verification
3. App Passwords → Generate
4. Copy password yang di-generate

**Alternatif: Mailtrap (untuk testing):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

### 3.5 Filesystem (Opsional)

Karena app ini pakai link drive untuk lampiran, filesystem tidak wajib. Tapi kalau perlu file upload:

**AWS S3:**
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=xxx
AWS_SECRET_ACCESS_KEY=xxx
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

---

## 🔑 Step 4: Generate APP_KEY

Setelah deploy pertama kali:

1. Buka Render dashboard → Web Service → **Shell**
2. Atau via Render CLI:
   ```bash
   render shell lkh-kua
   ```
3. Generate key:
   ```bash
   php artisan key:generate --show
   ```
4. Copy output (format: `base64:xxx...`)
5. Paste ke Environment Variables di Render dashboard:
   - Key: `APP_KEY`
   - Value: `base64:xxx...` (paste hasil generate)
6. **Redeploy** service (atau tunggu auto-redeploy)

---

## 🗃️ Step 5: Setup Database

### 5.1 Run Migrations

Via Render Shell:
```bash
php artisan migrate --force
```

### 5.2 Seed Data (Optional)

```bash
php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder --force
```

### 5.3 Create Storage Link

```bash
php artisan storage:link
```

---

## ⚡ Step 6: Optimize for Production

Via Render Shell:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

*Note: Ini sudah otomatis di-build command, tapi kalau perlu manual bisa run lagi.*

---

## ✅ Step 7: Verify Deployment

1. **Check URL**: Akses `https://your-app.onrender.com`
2. **Test Login**: Coba login dengan user yang sudah di-seed
3. **Test Features**: 
   - Create LKH
   - Submit LKH
   - Approve/Reject (kalau sebagai Kepala KUA)
4. **Check Logs**: Render dashboard → Logs (untuk error tracking)

---

## 🔄 Step 8: Auto-Deploy Setup

Setelah setup pertama, setiap push ke `main` branch akan auto-deploy:

1. Push code ke GitHub
2. Render akan auto-detect changes
3. Run build command
4. Deploy ke production
5. Notifikasi via email (kalau di-enable)

**Monitor deployment:**
- Render dashboard → Events (lihat deployment history)
- Logs (lihat build & runtime logs)

---

## 🐛 Troubleshooting

### Error: "APP_KEY not set"

**Solusi:**
- Pastikan `APP_KEY` sudah di-set di environment variables
- Format harus: `base64:xxx...`
- Redeploy setelah set environment variable

### Error: "Database connection failed"

**Solusi:**
- Cek environment variables database (host, port, username, password)
- Untuk PlanetScale: pastikan SSL enabled (`MYSQL_ATTR_SSL_CA`)
- Test connection via Render Shell:
  ```bash
  php artisan tinker
  DB::connection()->getPdo();
  ```

### Error: "Storage link not found"

**Solusi:**
- Run `php artisan storage:link` via Render Shell
- Atau pakai external storage (S3) untuk file upload

### Error: "Class not found" atau "Autoload error"

**Solusi:**
- Pastikan `composer install` di build command
- Clear cache: `php artisan config:clear && php artisan cache:clear`
- Redeploy

### Service Sleep (Free Tier)

**Problem:**
- Render free tier sleep setelah 15 menit tidak aktif
- First request setelah sleep akan lambat (cold start)

**Solusi:**
- Ini normal untuk free tier
- Pertimbangkan upgrade ke paid plan ($7/bulan) untuk always-on
- Atau pakai external cron service untuk ping service setiap 10 menit:
  - [Cron-job.org](https://cron-job.org) (gratis)
  - [UptimeRobot](https://uptimerobot.com) (gratis)

### Build Timeout

**Problem:**
- Build command terlalu lama (>20 menit)

**Solusi:**
- Optimize build command (remove unnecessary steps)
- Check apakah `npm ci` atau `composer install` terlalu lama
- Consider upgrade ke paid plan (faster builds)

### Email Not Sending

**Solusi:**
- Cek SMTP credentials (username, password)
- Untuk Gmail: pastikan pakai App Password, bukan password biasa
- Test via tinker:
  ```bash
  php artisan tinker
  Mail::raw('Test email', function($msg) { $msg->to('your-email@example.com')->subject('Test'); });
  ```

---

## 📊 Monitoring & Logs

### View Logs

1. **Render Dashboard** → Web Service → **Logs**
2. Filter by:
   - Build logs (saat deployment)
   - Runtime logs (saat aplikasi jalan)
   - Error logs

### Metrics

Render dashboard menampilkan:
- **CPU Usage**
- **Memory Usage**
- **Request Count**
- **Response Time**

---

## 🔐 Security Best Practices

1. **Never commit `.env`** ke Git
2. **Use strong passwords** untuk database
3. **Enable SSL** (otomatis di Render)
4. **Set `APP_DEBUG=false`** di production
5. **Use App Passwords** untuk Gmail (bukan password biasa)
6. **Regular updates** untuk dependencies

---

## 💡 Tips & Tricks

### 1. Custom Domain

1. Di Render dashboard → Web Service → **Settings** → **Custom Domain**
2. Add domain: `app.yourdomain.com`
3. Update DNS records sesuai instruksi Render
4. SSL akan otomatis di-generate

### 2. Environment-Specific Config

Gunakan environment variables untuk different environments:
- `APP_ENV=production` untuk production
- `APP_DEBUG=false` untuk production
- Different database untuk staging vs production

### 3. Scheduled Tasks (Cron Jobs)

Render tidak support cron jobs langsung. Solusi:

**Opsi A: External Cron Service**
- [Cron-job.org](https://cron-job.org) - gratis
- Setup HTTP request ke endpoint khusus
- Contoh: `https://your-app.onrender.com/cron/daily-report`

**Opsi B: Render Cron Jobs (Paid)**
- Upgrade ke paid plan
- Setup cron job di Render dashboard

### 4. Database Backups

**PlanetScale:**
- Auto-backups included
- Branching untuk testing

**Render PostgreSQL:**
- Manual backup via pg_dump
- Atau upgrade ke paid plan untuk auto-backups

### 5. Performance Optimization

- ✅ Enable caching (config, route, view cache)
- ✅ Use CDN untuk static assets (Vite sudah handle)
- ✅ Optimize database queries (pakai indexes)
- ✅ Monitor slow queries via logs

---

## 📚 Additional Resources

- [Render Documentation](https://render.com/docs)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [PlanetScale Documentation](https://planetscale.com/docs)
- [SETUP_PLANETSCALE.md](SETUP_PLANETSCALE.md) - Setup MySQL gratis

---

## ✅ Deployment Checklist

- [ ] Code pushed ke GitHub
- [ ] Render account created
- [ ] Web service created di Render
- [ ] Database setup (PlanetScale atau Render PostgreSQL)
- [ ] Environment variables configured
- [ ] APP_KEY generated dan di-set
- [ ] Migrations run
- [ ] Seed data (optional)
- [ ] Storage link created
- [ ] Test aplikasi
- [ ] Email configuration tested
- [ ] Custom domain setup (optional)
- [ ] Monitoring setup
- [ ] Documentation updated

---

## 🎉 You're Done!

Aplikasi Laravel kamu sekarang sudah live di Render! 🚀

**Next Steps:**
1. Share URL dengan team
2. Setup custom domain (optional)
3. Monitor logs untuk errors
4. Setup backups (kalau perlu)
5. Enjoy your deployed app! 🎊

---

**Need Help?**
- Check [Render Documentation](https://render.com/docs)
- Check [Laravel Documentation](https://laravel.com/docs)
- Open issue di GitHub repository

*Happy deploying!* ✨
