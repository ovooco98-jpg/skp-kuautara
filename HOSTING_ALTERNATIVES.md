# Alternatif Hosting Gratis untuk Laravel

Karena Railway sudah limit, berikut alternatif hosting gratis yang cocok untuk Laravel:

## 🏆 Rekomendasi Terbaik

### 1. **Render** ⭐ (Paling Direkomendasikan)

**Kelebihan:**
- ✅ Free tier tersedia (750 jam/bulan)
- ✅ Auto-deploy dari GitHub
- ✅ Support Laravel dengan baik
- ✅ PostgreSQL gratis (90 hari, bisa extend)
- ✅ SSL gratis otomatis
- ✅ Environment variables management
- ✅ Logs monitoring

**Limit Free Tier:**
- 750 jam/bulan (cukup untuk 1 service)
- Service sleep setelah 15 menit tidak aktif (wake up dalam beberapa detik)
- PostgreSQL: 90 hari gratis, lalu $7/bulan

**Setup:**
1. Daftar di [render.com](https://render.com)
2. Connect GitHub repository
3. New → Web Service → pilih repo
4. Settings:
   - **Build Command**: `composer install --optimize-autoloader --no-dev && npm ci && npm run build`
   - **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`
   - **Environment**: PHP
5. Add PostgreSQL database (gratis 90 hari)
6. Setup environment variables

**Environment Variables:**
```env
APP_NAME="LKH KUA"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-app.onrender.com

DB_CONNECTION=pgsql
DB_HOST=your-postgres-host
DB_PORT=5432
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

# ... lainnya sama seperti Railway
```

**File yang perlu dibuat: `render.yaml`:**
```yaml
services:
  - type: web
    name: lkh-kua
    env: php
    buildCommand: composer install --optimize-autoloader --no-dev && npm ci && npm run build
    startCommand: php artisan serve --host=0.0.0.0 --port=$PORT
    envVars:
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
```

---

### 2. **Fly.io** ⭐

**Kelebihan:**
- ✅ Free tier dengan 3 shared-cpu VMs
- ✅ Global edge network
- ✅ Auto-scaling
- ✅ PostgreSQL gratis (3GB storage)
- ✅ SSL gratis
- ✅ Deploy dari GitHub

**Limit Free Tier:**
- 3 shared-cpu VMs
- 3GB persistent volume
- 160GB outbound data transfer/bulan

**Setup:**
1. Install Fly CLI: `curl -L https://fly.io/install.sh | sh`
2. Daftar: `fly auth signup`
3. Di project: `fly launch`
4. Setup database: `fly postgres create`

**File `fly.toml` (akan dibuat otomatis):**
```toml
app = "lkh-kua"
primary_region = "sin"  # Singapore

[build]
  builder = "paketobuildpacks/builder:base"

[env]
  APP_ENV = "production"
  APP_DEBUG = "false"

[[services]]
  http_checks = []
  internal_port = 8080
  processes = ["app"]
  protocol = "tcp"
  script_checks = []
```

---

### 3. **Heroku** (Alternatif)

**Kelebihan:**
- ✅ Platform yang mature
- ✅ Add-ons banyak
- ✅ PostgreSQL gratis (hobby-dev)

**Limit:**
- Free tier sudah dihapus (2022)
- Harus pakai paid plan ($5/bulan minimum)

**Tidak direkomendasikan** karena tidak ada free tier.

---

### 4. **000webhost** (Basic Hosting)

**Kelebihan:**
- ✅ 100% gratis
- ✅ Unlimited bandwidth
- ✅ cPanel included

**Limit:**
- Shared hosting (tidak ideal untuk Laravel)
- Tidak support Composer dengan baik
- Tidak ada CLI access
- Database terbatas

**Tidak direkomendasikan** untuk Laravel modern.

---

### 5. **AlwaysData** (Free Tier)

**Kelebihan:**
- ✅ Free tier tersedia
- ✅ PHP 8.2 support
- ✅ MySQL gratis
- ✅ SSH access

**Limit:**
- 100MB storage
- 50MB database
- 1 domain
- Limited resources

**Bisa digunakan** untuk development/testing kecil.

---

## 🗄️ Database Gratis (Terpisah)

Jika hosting tidak include database gratis, gunakan:

### 1. **Supabase** (PostgreSQL)
- ✅ 500MB database gratis
- ✅ Auto backups
- ✅ Real-time features
- ✅ REST API otomatis

### 2. **PlanetScale** (MySQL)
- ✅ 1 database gratis
- ✅ 1GB storage
- ✅ Branching (seperti Git)
- ✅ Serverless

### 3. **Neon** (PostgreSQL)
- ✅ 0.5GB gratis
- ✅ Serverless PostgreSQL
- ✅ Auto-scaling

### 4. **Aiven** (PostgreSQL/MySQL)
- ✅ Free tier tersedia
- ✅ Managed database

---

## 📊 Perbandingan

| Platform | Free Tier | Database | Auto-Deploy | Laravel Support | Rekomendasi |
|----------|-----------|----------|-------------|-----------------|-------------|
| **Render** | ✅ 750 jam/bulan | ✅ 90 hari | ✅ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Fly.io** | ✅ 3 VMs | ✅ 3GB | ✅ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Heroku** | ❌ | ✅ (paid) | ✅ | ⭐⭐⭐⭐ | ⭐⭐ |
| **000webhost** | ✅ | ✅ | ❌ | ⭐⭐ | ⭐ |
| **AlwaysData** | ✅ | ✅ | ❌ | ⭐⭐⭐ | ⭐⭐⭐ |

---

## 🚀 Setup Render (Step by Step)

### Step 1: Persiapkan Repository
Pastikan code sudah di GitHub dan siap untuk deploy.

### Step 2: Buat File `render.yaml`
Buat file di root project:

```yaml
services:
  - type: web
    name: lkh-kua
    env: php
    plan: free
    buildCommand: composer install --optimize-autoloader --no-dev && npm ci && npm run build
    startCommand: php artisan serve --host=0.0.0.0 --port=$PORT
    envVars:
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
      - key: LOG_CHANNEL
        value: stderr
```

### Step 3: Deploy ke Render
1. Login ke [render.com](https://render.com)
2. New → Web Service
3. Connect GitHub → pilih repository
4. Render akan auto-detect `render.yaml`
5. Atau manual setup:
   - **Name**: lkh-kua
   - **Environment**: PHP
   - **Build Command**: `composer install --optimize-autoloader --no-dev && npm ci && npm run build`
   - **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`
   - **Plan**: Free

### Step 4: Setup Database

**Opsi A: MySQL dengan PlanetScale** ⭐ (Direkomendasikan - Gratis Permanen)
1. Daftar di [planetscale.com](https://planetscale.com)
2. Create database baru
3. Copy connection string
4. Lihat [SETUP_PLANETSCALE.md](SETUP_PLANETSCALE.md) untuk detail lengkap

**Opsi B: PostgreSQL dengan Render** (90 hari gratis)
1. Di Render dashboard: New → PostgreSQL
2. Name: lkh-kua-db
3. Plan: Free (90 hari)
4. Copy connection string

### Step 5: Environment Variables
Di Web Service → Environment, tambahkan:

```env
APP_NAME="LKH KUA"
APP_ENV=production
APP_KEY=  # Generate dengan: php artisan key:generate
APP_DEBUG=false
APP_URL=https://your-app.onrender.com

# Opsi 1: MySQL dengan PlanetScale (Direkomendasikan - Gratis Permanen)
DB_CONNECTION=mysql
DB_HOST=your-host.psdb.cloud
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password
DB_SSL_CA=/etc/ssl/certs/ca-certificates.crt

# Opsi 2: PostgreSQL dengan Render (90 hari gratis)
# DB_CONNECTION=pgsql
# DB_HOST=your-postgres-host.onrender.com
# DB_PORT=5432
# DB_DATABASE=your-database
# DB_USERNAME=your-username
# DB_PASSWORD=your-password

SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database

# Mail (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kua-banjarutara.go.id
MAIL_FROM_NAME="${APP_NAME}"

APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
```

### Step 6: Generate APP_KEY
1. Buka Render Shell (di dashboard)
2. Jalankan: `php artisan key:generate`
3. Copy `APP_KEY` ke environment variables

### Step 7: Run Migrations
Di Render Shell:
```bash
php artisan migrate --force
php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder
php artisan storage:link
```

### Step 8: Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🚀 Setup Fly.io (Step by Step)

### Step 1: Install Fly CLI
```bash
# Windows (PowerShell)
iwr https://fly.io/install.ps1 -useb | iex

# Mac/Linux
curl -L https://fly.io/install.sh | sh
```

### Step 2: Login
```bash
fly auth login
```

### Step 3: Launch App
```bash
cd your-project
fly launch
```

### Step 4: Setup Database
```bash
fly postgres create --name lkh-kua-db --region sin
fly postgres attach lkh-kua-db
```

### Step 5: Deploy
```bash
fly deploy
```

---

## 💡 Tips untuk Free Tier

### 1. **Render Sleep Mode**
- Service akan sleep setelah 15 menit tidak aktif
- Wake up dalam beberapa detik saat ada request
- Untuk menghindari sleep, bisa setup uptime monitor (UptimeRobot gratis)

### 2. **Database Free Tier**
- Render PostgreSQL: 90 hari gratis
- Setelah itu, bisa pindah ke Supabase/Neon (gratis)
- Atau upgrade ke paid ($7/bulan)

### 3. **Optimasi untuk Free Tier**
- Gunakan caching untuk mengurangi query
- Optimasi assets (minify, compress)
- Gunakan CDN untuk static files (Cloudflare Pages gratis)

### 4. **Monitoring**
- Gunakan UptimeRobot (gratis) untuk monitor uptime
- Setup email alerts

---

## 🔄 Migrasi dari Railway ke Render

1. **Export Environment Variables** dari Railway
2. **Setup Render** sesuai guide di atas
3. **Export Database** dari Railway:
   ```bash
   # Di Railway
   mysqldump -u user -p database > backup.sql
   ```
4. **Import ke Render PostgreSQL**:
   ```bash
   # Convert MySQL ke PostgreSQL jika perlu
   # Atau gunakan tool seperti pgloader
   psql -h host -U user -d database < backup.sql
   ```
5. **Update APP_URL** di environment variables
6. **Test semua fitur**

---

## 📝 Checklist Deployment

- [ ] Code sudah di GitHub
- [ ] Environment variables sudah diset
- [ ] Database sudah dibuat
- [ ] APP_KEY sudah di-generate
- [ ] Migrations sudah di-run
- [ ] Seeder sudah di-run
- [ ] Storage link sudah dibuat
- [ ] Config/Route/View sudah di-cache
- [ ] APP_URL sudah benar
- [ ] Test semua fitur
- [ ] Setup custom domain (opsional)

---

## 🆘 Troubleshooting

### Render: Service tidak bisa start
- Check logs di Render dashboard
- Pastikan `startCommand` benar
- Check environment variables

### Database connection error
- Pastikan database sudah running
- Check connection string
- Pastikan firewall/network settings

### Slow first load
- Normal untuk free tier (cold start)
- Bisa setup uptime monitor untuk keep alive

---

## 📚 Resources

- [Render Documentation](https://render.com/docs)
- [Fly.io Documentation](https://fly.io/docs)
- [Supabase Documentation](https://supabase.com/docs)
- [PlanetScale Documentation](https://planetscale.com/docs)

---

## 🎯 Rekomendasi Final

**Untuk Production dengan MySQL:**
1. **Render** + **PlanetScale MySQL** ⭐ (Paling Direkomendasikan)
   - ✅ Render: 750 jam/bulan gratis
   - ✅ PlanetScale: 1GB MySQL gratis permanen
   - ✅ Tidak perlu migrate ke PostgreSQL
   - ✅ Tetap pakai MySQL seperti sekarang

**Untuk Production dengan PostgreSQL:**
2. **Render** + **Supabase PostgreSQL**
   - ✅ Render: 750 jam/bulan gratis
   - ✅ Supabase: 500MB PostgreSQL gratis
   - ✅ Perlu ubah DB_CONNECTION=pgsql

**Alternatif:**
3. **Fly.io** + **PlanetScale MySQL** (gratis)
4. **Fly.io** + **Supabase PostgreSQL** (gratis)

**Untuk Development:**
- **AlwaysData** atau **000webhost** (jika hanya testing)

**Best Choice: Render + PlanetScale MySQL** karena:
- ✅ Free tier yang cukup
- ✅ Auto-deploy
- ✅ **MySQL gratis permanen** (tidak perlu migrate)
- ✅ Tetap pakai MySQL seperti development
- ✅ Easy setup
- ✅ Good documentation
