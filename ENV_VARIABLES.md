# Environment Variables Reference

Dokumentasi lengkap untuk semua environment variables yang digunakan di aplikasi LKH KUA.

## 📋 Required Variables (Wajib)

### Application
```env
APP_NAME="LKH KUA"
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_URL=https://your-app.railway.app
```

**APP_KEY**: Generate dengan `php artisan key:generate`

### Database (MySQL)
```env
DB_CONNECTION=mysql
DB_HOST=your-mysql-host.railway.app
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-password
```

**Untuk Railway**: Gunakan `${{MySQL.MYSQLHOST}}` dll untuk auto-reference

## 📧 Email Configuration (Optional tapi Recommended)

### Gmail SMTP
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kua-banjarutara.go.id
MAIL_FROM_NAME="${APP_NAME}"
```

**Cara mendapatkan Gmail App Password:**
1. Aktifkan 2-Step Verification
2. Buka: https://myaccount.google.com/apppasswords
3. Generate password untuk "Mail"
4. Gunakan password tersebut di `MAIL_PASSWORD`

### Mailtrap (Development)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

## 🔐 Session & Cache

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_DRIVER=file
CACHE_PREFIX=
```

**Untuk Production**: Pertimbangkan menggunakan Redis untuk cache:
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 📁 File Storage

```env
FILESYSTEM_DISK=local
```

**Untuk Production dengan S3**:
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## 🔄 Queue

```env
QUEUE_CONNECTION=database
```

**Untuk Production**: Gunakan Redis atau database:
```env
QUEUE_CONNECTION=redis
# atau
QUEUE_CONNECTION=database
```

## 📝 Logging

```env
LOG_CHANNEL=stack
LOG_LEVEL=error
LOG_DEPRECATIONS_CHANNEL=null
```

**Untuk Production**: 
- `LOG_LEVEL=error` (hanya error)
- Atau gunakan external service (Sentry, Logtail, dll)

## 🌐 Timezone & Locale

```env
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID
```

## 🔒 Security

```env
APP_DEBUG=false  # HARUS false di production!
APP_ENV=production
```

## 📊 Example .env untuk Railway

```env
APP_NAME="LKH KUA"
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_URL=https://lkh-kua.railway.app

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

APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
```

## ✅ Checklist Environment Variables

Sebelum deploy, pastikan:

- [ ] `APP_KEY` sudah di-generate
- [ ] `APP_DEBUG=false` untuk production
- [ ] `APP_ENV=production`
- [ ] `APP_URL` sesuai dengan domain aplikasi
- [ ] Database credentials benar
- [ ] Email SMTP sudah dikonfigurasi (jika perlu)
- [ ] Session driver sudah diset (database recommended)
- [ ] Queue connection sudah diset

## 🔍 Testing Environment Variables

Setelah setup, test dengan:

```bash
php artisan tinker
```

```php
config('app.env')  // Should return 'production'
config('app.debug') // Should return false
config('database.default') // Should return 'mysql'
DB::connection()->getPdo(); // Should connect successfully
```

