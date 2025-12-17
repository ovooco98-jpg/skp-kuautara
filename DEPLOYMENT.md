# Panduan Deployment LKH KUA

## ⚠️ Catatan Penting

**Vercel tidak ideal untuk Laravel** karena:
- Vercel dirancang untuk serverless functions (Next.js, Nuxt, dll)
- Laravel membutuhkan server PHP yang berjalan terus-menerus
- Tidak ada persistent storage untuk file uploads
- Session dan cache membutuhkan storage yang persisten

## 🎯 Platform yang Direkomendasikan

### Untuk Free Tier:
1. **Render** ⭐ (Paling Direkomendasikan) - 750 jam/bulan gratis
2. **Fly.io** ⭐ - 3 VMs gratis
3. **Railway** - Free tier terbatas (sudah limit)

### Untuk Paid:
- Railway ($5/bulan)
- Heroku ($5/bulan)
- DigitalOcean App Platform

**📖 Lihat [HOSTING_ALTERNATIVES.md](HOSTING_ALTERNATIVES.md) untuk detail lengkap semua alternatif hosting gratis.**

---

## ☁️ Deployment ke Render (Rekomendasi untuk Free Tier)

Render adalah alternatif terbaik untuk free tier karena:
- ✅ 750 jam/bulan gratis (cukup untuk 1 service)
- ✅ PostgreSQL gratis 90 hari (bisa extend atau pindah ke Supabase)
- ✅ Auto-deploy dari GitHub
- ✅ SSL gratis otomatis
- ✅ Support Laravel dengan baik

### Quick Start Render:

1. **Setup Render Account**
   - Daftar di [render.com](https://render.com)
   - Connect GitHub account

2. **Deploy Web Service**
   - New → Web Service
   - Pilih repository `lkh-kua`
   - Render akan auto-detect `render.yaml`
   - Atau manual setup (lihat `render.yaml`)

3. **Setup PostgreSQL**
   - New → PostgreSQL
   - Plan: Free (90 hari)
   - Copy connection details

4. **Environment Variables**
   - Setup sesuai `ENV_VARIABLES.md`
   - Gunakan PostgreSQL connection string

5. **Generate APP_KEY & Run Migrations**
   - Buka Render Shell
   - `php artisan key:generate`
   - `php artisan migrate --force`
   - `php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder`

**📖 Lihat:**
- [HOSTING_ALTERNATIVES.md](HOSTING_ALTERNATIVES.md) - Panduan lengkap semua platform
- [SETUP_PLANETSCALE.md](SETUP_PLANETSCALE.md) - Setup MySQL gratis dengan PlanetScale (Direkomendasikan)

---

## 🚂 Deployment ke Railway (Jika Masih Ada Credit)

Railway adalah platform yang sangat cocok untuk Laravel karena:
- ✅ Mendukung PHP/Laravel dengan baik
- ✅ Bisa deploy Laravel app + MySQL di satu platform
- ✅ Auto-deploy dari GitHub
- ✅ Environment variables management
- ✅ Free tier tersedia

### Langkah-langkah Deployment:

#### 1. Setup Railway Account
1. Daftar di [railway.app](https://railway.app)
2. Login dengan GitHub account

#### 2. Buat Project Baru
1. Klik "New Project"
2. Pilih "Deploy from GitHub repo"
3. Pilih repository `lkh-kua`

#### 3. Setup MySQL Database
1. Di project dashboard, klik "New"
2. Pilih "Database" → "MySQL"
3. Railway akan otomatis membuat database MySQL
4. Catat connection details:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`

#### 4. Setup Laravel Service
1. Di project dashboard, klik "New"
2. Pilih "GitHub Repo" → pilih `lkh-kua`
3. Railway akan otomatis detect Laravel

#### 5. Konfigurasi Environment Variables
Di service Laravel, tambahkan environment variables berikut:

```env
APP_NAME="LKH KUA"
APP_ENV=production
APP_KEY=  # Generate dengan: php artisan key:generate
APP_DEBUG=false
APP_URL=https://your-app-name.railway.app

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120

QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kua-banjarutara.go.id
MAIL_FROM_NAME="${APP_NAME}"
```

**Cara mendapatkan MySQL variables:**
- Railway otomatis menyediakan `${{MySQL.MYSQLHOST}}` dll
- Atau gunakan values langsung dari MySQL service

#### 6. Generate APP_KEY
1. Buka Railway CLI atau gunakan Railway web terminal
2. Jalankan: `php artisan key:generate`
3. Copy `APP_KEY` yang dihasilkan ke environment variables

#### 7. Run Migrations
Di Railway web terminal atau via CLI:
```bash
php artisan migrate --force
php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder
```

#### 8. Setup Storage Link
```bash
php artisan storage:link
```

#### 9. Optimize untuk Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 10. Setup Custom Domain (Opsional)
1. Di service settings, pilih "Settings" → "Networking"
2. Klik "Generate Domain" atau tambahkan custom domain
3. Update `APP_URL` di environment variables

---

## ☁️ Alternatif: Deployment ke Vercel (Tidak Direkomendasikan)

Jika tetap ingin menggunakan Vercel, Anda perlu:
1. Menggunakan Laravel Vapor atau adapter serverless
2. Setup external storage (S3) untuk file uploads
3. Setup external cache (Redis)
4. Setup external session storage

**Konfigurasi Vercel:**

File `vercel.json` sudah disediakan, tapi perlu banyak penyesuaian.

---

## 🔧 Setup Database di Railway

### Manual Setup (jika tidak pakai Railway MySQL):

1. Buat MySQL service di Railway
2. Dapatkan connection string
3. Update environment variables di Laravel service

### Connection String Format:
```
mysql://user:password@host:port/database
```

---

## 📧 Setup Email (Gmail SMTP)

1. Aktifkan 2-Step Verification di Google Account
2. Generate App Password:
   - Buka: https://myaccount.google.com/apppasswords
   - Pilih "Mail" dan "Other (Custom name)"
   - Masukkan nama: "LKH KUA"
   - Copy password yang dihasilkan
3. Gunakan password tersebut di `MAIL_PASSWORD`

---

## 🔐 Security Checklist

- [ ] `APP_DEBUG=false` di production
- [ ] `APP_ENV=production`
- [ ] Generate `APP_KEY` yang unik
- [ ] Gunakan HTTPS (Railway otomatis)
- [ ] Setup proper CORS jika perlu
- [ ] Review file permissions
- [ ] Setup backup database secara berkala

---

## 🚀 Post-Deployment

1. **Test semua fitur:**
   - Login/Logout
   - Create LKH
   - Upload file
   - Generate laporan

2. **Monitor logs:**
   - Railway dashboard → Logs
   - Check untuk errors

3. **Setup monitoring:**
   - Railway memiliki built-in monitoring
   - Atau gunakan external service (Sentry, dll)

---

## 📝 Troubleshooting

### Error: "No application encryption key has been specified"
- Generate `APP_KEY`: `php artisan key:generate`

### Error: "SQLSTATE[HY000] [2002] Connection refused"
- Check MySQL service running
- Verify database credentials
- Check network settings

### Error: "Storage link not found"
- Run: `php artisan storage:link`

### Error: "Class not found"
- Run: `composer install --optimize-autoloader`
- Run: `php artisan config:clear`

---

## 🔄 Auto-Deploy

Railway otomatis deploy setiap push ke branch yang dikonfigurasi (default: `main`).

Untuk setup manual deploy:
1. Railway dashboard → Settings
2. Pilih branch untuk auto-deploy
3. Setup build command jika perlu

---

## 💰 Pricing

**Railway Free Tier:**
- $5 credit per bulan
- Cukup untuk development/small production

**Railway Pro:**
- $20/bulan
- Unlimited usage
- Better support

---

## 📚 Resources

- [Railway Documentation](https://docs.railway.app)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Railway PHP Guide](https://docs.railway.app/languages/php)
