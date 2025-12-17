# Setup PlanetScale MySQL untuk LKH KUA

Panduan lengkap setup PlanetScale sebagai database MySQL gratis untuk aplikasi LKH KUA.

## 🎯 Mengapa PlanetScale?

- ✅ **MySQL gratis 1GB** (permanen, tidak ada expiry)
- ✅ Serverless (auto-scaling)
- ✅ Branching seperti Git (untuk testing)
- ✅ Compatible dengan Laravel
- ✅ Tidak perlu migrate dari MySQL ke PostgreSQL

## 📝 Step-by-Step Setup

### Step 1: Daftar PlanetScale

1. Buka [planetscale.com](https://planetscale.com)
2. Klik "Sign up" atau "Get started"
3. Login dengan GitHub (recommended) atau email
4. Verifikasi email jika perlu

### Step 2: Create Database

1. Setelah login, klik "Create database"
2. **Database name**: `lkh-kua` (atau nama lain)
3. **Region**: Pilih yang terdekat (Singapore untuk Indonesia)
4. **Plan**: Pilih "Hobby" (gratis)
5. Klik "Create database"

### Step 3: Get Connection String

1. Setelah database dibuat, klik database name
2. Klik tab "Connect"
3. Pilih "PHP" atau "General"
4. Copy connection string, contoh:
   ```
   mysql://username:password@host.psdb.cloud:3306/database?sslmode=require
   ```

### Step 4: Create Password

1. Di halaman Connect, klik "New password"
2. Beri nama password (contoh: "production")
3. Copy password yang di-generate (simpan dengan aman!)

### Step 5: Setup di Render/Fly.io

**Jika menggunakan Render:**

1. Buka Render dashboard → Web Service
2. Klik "Environment"
3. Tambahkan environment variables:

```env
DB_CONNECTION=mysql
DB_HOST=your-host.psdb.cloud
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-username
DB_PASSWORD=your-password
DB_SSL_CA=/etc/ssl/certs/ca-certificates.crt
```

**Atau gunakan connection string langsung:**
```env
DB_URL=mysql://username:password@host.psdb.cloud:3306/database?sslmode=require
```

Laravel akan otomatis parse `DB_URL` jika diset.

### Step 6: Update Laravel Config (Opsional)

Jika perlu custom SSL, update `config/database.php`:

```php
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'options' => [
        PDO::MYSQL_ATTR_SSL_CA => env('DB_SSL_CA'),
    ],
],
```

### Step 7: Test Connection

Di Render Shell atau local:

```bash
php artisan tinker
```

```php
DB::connection()->getPdo();
// Jika berhasil, akan return PDO object
```

### Step 8: Run Migrations

```bash
php artisan migrate --force
php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder
```

## 🔐 Security Best Practices

1. **Jangan commit password ke Git**
   - Gunakan environment variables
   - Pastikan `.env` di `.gitignore`

2. **Gunakan Branch untuk Testing**
   - PlanetScale support branching
   - Buat branch untuk testing migrations
   - Merge ke main setelah test berhasil

3. **Rotate Passwords**
   - Ganti password secara berkala
   - Buat password baru di PlanetScale dashboard

## 📊 PlanetScale Features

### Branching (Seperti Git)

1. **Create Branch:**
   ```bash
   # Di PlanetScale dashboard
   # Klik "Branches" → "Create branch"
   ```

2. **Test Migrations di Branch:**
   - Connect ke branch database
   - Test migrations
   - Jika OK, merge ke main

3. **Merge Branch:**
   - Di dashboard, klik "Merge" pada branch
   - PlanetScale akan merge schema changes

### Monitoring

- **Metrics**: Lihat query performance
- **Logs**: Query logs untuk debugging
- **Insights**: Database insights dan recommendations

## 🚨 Troubleshooting

### Error: SSL Connection Required

**Solusi:**
Tambahkan `DB_SSL_CA` di environment variables:
```env
DB_SSL_CA=/etc/ssl/certs/ca-certificates.crt
```

Atau di `config/database.php`:
```php
'options' => [
    PDO::MYSQL_ATTR_SSL_CA => env('DB_SSL_CA'),
],
```

### Error: Connection Timeout

**Solusi:**
1. Check firewall settings
2. Pastikan IP whitelist di PlanetScale (jika ada)
3. Check network connectivity

### Error: Access Denied

**Solusi:**
1. Check username dan password
2. Pastikan password masih valid (tidak expired)
3. Buat password baru jika perlu

## 💰 Pricing

**Hobby Plan (Free):**
- ✅ 1 database
- ✅ 1GB storage
- ✅ 1 billion row reads/month
- ✅ 10 million row writes/month
- ✅ Unlimited branches
- ✅ **Gratis permanen**

**Scaler Plan ($29/bulan):**
- 10GB storage
- Unlimited reads/writes
- Better performance

## 🔄 Migrasi dari Local MySQL

Jika sudah punya data di local MySQL:

1. **Export dari Local:**
   ```bash
   mysqldump -u root -p lkh_kua > backup.sql
   ```

2. **Import ke PlanetScale:**
   ```bash
   mysql -h your-host.psdb.cloud -u username -p database < backup.sql
   ```

   Atau gunakan PlanetScale CLI:
   ```bash
   pscale db import database_name backup.sql
   ```

## 📚 Resources

- [PlanetScale Documentation](https://planetscale.com/docs)
- [Laravel Database Configuration](https://laravel.com/docs/database)
- [PlanetScale PHP Guide](https://planetscale.com/docs/tutorials/connect-any-application)

## ✅ Checklist

- [ ] Daftar PlanetScale
- [ ] Create database
- [ ] Create password
- [ ] Copy connection string
- [ ] Setup environment variables di hosting
- [ ] Test connection
- [ ] Run migrations
- [ ] Run seeders
- [ ] Test aplikasi
- [ ] Setup backup strategy (opsional)

---

**Tips:** PlanetScale sangat cocok untuk Laravel karena:
- ✅ MySQL native (tidak perlu migrate)
- ✅ Gratis permanen
- ✅ Serverless (auto-scaling)
- ✅ Branching untuk safe migrations

