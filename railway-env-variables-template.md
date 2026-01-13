# 📋 Railway Environment Variables Template

*Copy-paste ready environment variables untuk Railway deployment*

## 🚀 Quick Setup

### Cara 1: Copy Manual (Recommended)

Copy setiap variable di bawah ini ke Railway Dashboard → Service → Variables:

```env
APP_NAME=LKH KUA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lkh-kua-production.up.railway.app
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_KEY=base64:CHANGE_THIS_WITH_GENERATED_KEY

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
LOG_LEVEL=error

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME=LKH KUA
```

### Cara 2: Import dari JSON

File `railway-env-variables.json` sudah tersedia. Kamu bisa:
1. Buka file `railway-env-variables.json`
2. Copy semua isinya
3. Atau gunakan Railway CLI untuk import (jika support)

---

## 📝 Penjelasan Variables

### App Configuration

| Variable | Value | Keterangan |
|----------|-------|------------|
| `APP_NAME` | `LKH KUA` | Nama aplikasi |
| `APP_ENV` | `production` | Environment (production/development) |
| `APP_DEBUG` | `false` | Debug mode (false untuk production) |
| `APP_URL` | `https://...` | URL aplikasi Railway (ganti dengan URL kamu) |
| `APP_TIMEZONE` | `Asia/Jakarta` | Timezone aplikasi |
| `APP_LOCALE` | `id` | Bahasa default (id = Indonesia) |
| `APP_KEY` | `base64:...` | **WAJIB DI-GENERATE!** Lihat step 6 di tutorial |

### Database Configuration

| Variable | Value | Keterangan |
|----------|-------|------------|
| `DB_CONNECTION` | `mysql` | Tipe database (mysql/pgsql) |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` | **Reference ke MySQL service** |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` | **Reference ke MySQL service** |
| `DB_DATABASE` | `${{MySQL.MYSQLDATABASE}}` | **Reference ke MySQL service** |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` | **Reference ke MySQL service** |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` | **Reference ke MySQL service** |

**💡 Tips:**
- Railway variable references menggunakan format `${{ServiceName.VariableName}}`
- `MySQL` adalah nama service MySQL di Railway
- Railway akan otomatis inject values dari MySQL service

### Session & Cache

| Variable | Value | Keterangan |
|----------|-------|------------|
| `SESSION_DRIVER` | `database` | Session storage (database/file/cookie) |
| `SESSION_LIFETIME` | `120` | Session lifetime dalam menit |
| `CACHE_STORE` | `database` | Cache storage (database/file/redis) |
| `QUEUE_CONNECTION` | `database` | Queue driver (database/redis/sync) |

### Logging

| Variable | Value | Keterangan |
|----------|-------|------------|
| `LOG_CHANNEL` | `stack` | Log channel (stack/file/single) |
| `LOG_LEVEL` | `error` | Log level (debug/info/warning/error) |

### Mail Configuration (Opsional)

| Variable | Value | Keterangan |
|----------|-------|------------|
| `MAIL_MAILER` | `smtp` | Mail driver (smtp/mailgun/ses) |
| `MAIL_HOST` | `smtp.gmail.com` | SMTP host |
| `MAIL_PORT` | `587` | SMTP port |
| `MAIL_USERNAME` | `your-email@gmail.com` | **GANTI dengan email kamu** |
| `MAIL_PASSWORD` | `your-app-password` | **GANTI dengan Gmail App Password** |
| `MAIL_ENCRYPTION` | `tls` | Encryption (tls/ssl) |
| `MAIL_FROM_ADDRESS` | `your-email@gmail.com` | **GANTI dengan email kamu** |
| `MAIL_FROM_NAME` | `LKH KUA` | Nama pengirim email |

**💡 Cara dapat Gmail App Password:**
1. Buka Google Account → Security
2. Enable 2-Step Verification (kalau belum)
3. App Passwords → Generate
4. Pilih app: "Mail"
5. Pilih device: "Other (Custom name)" → ketik "Railway"
6. Copy password yang di-generate
7. Paste ke `MAIL_PASSWORD`

---

## ⚠️ Variables yang Harus Diubah

### 1. APP_URL
Ganti dengan URL Railway kamu:
- Format: `https://your-service-name.up.railway.app`
- Cek di Railway Dashboard → Service → Settings → Networking

### 2. APP_KEY
**WAJIB di-generate!** Jangan pakai value default.
- Generate via Railway terminal: `php artisan key:generate --show`
- Copy output dan paste ke `APP_KEY`

### 3. MAIL_USERNAME
Ganti dengan email Gmail kamu yang akan dipakai untuk kirim email.

### 4. MAIL_PASSWORD
Ganti dengan Gmail App Password (bukan password Gmail biasa!).

### 5. MAIL_FROM_ADDRESS
Biasanya sama dengan `MAIL_USERNAME`.

---

## 🔄 Variable References (Railway Feature)

Railway support **variable references** untuk connect services:

```env
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

**Keuntungan:**
- ✅ Otomatis update jika database credentials berubah
- ✅ Lebih aman (tidak perlu hardcode credentials)
- ✅ Mudah manage multiple services

**Cara pakai:**
1. Pastikan MySQL service sudah dibuat di Railway
2. Nama service harus `MySQL` (atau sesuaikan dengan nama service kamu)
3. Railway akan otomatis inject values

**Alternatif (Manual):**
Jika tidak pakai references, bisa set manual:
```env
DB_HOST=dpg-xxxxx-a.singapore-postgres.render.com
DB_PORT=3306
DB_DATABASE=lkh_kua
DB_USERNAME=root
DB_PASSWORD=your-password
```

---

## 📋 Checklist Setup

- [ ] Semua variables sudah di-copy ke Railway
- [ ] `APP_URL` sudah diubah sesuai URL Railway
- [ ] `APP_KEY` sudah di-generate dan di-set
- [ ] Database variables menggunakan references (`${{MySQL.XXX}}`)
- [ ] `MAIL_USERNAME` sudah diubah
- [ ] `MAIL_PASSWORD` sudah diubah (Gmail App Password)
- [ ] `MAIL_FROM_ADDRESS` sudah diubah
- [ ] MySQL service sudah dibuat di Railway
- [ ] Service sudah di-redeploy setelah set variables

---

## 🚀 Quick Copy Commands

### Copy Semua Variables (PowerShell)

```powershell
# Copy file content
Get-Content railway-env-variables.json | Set-Clipboard
```

### Copy Semua Variables (Bash)

```bash
# Copy file content
cat railway-env-variables.json | pbcopy  # Mac
cat railway-env-variables.json | xclip -selection clipboard  # Linux
```

---

## 📚 Related Files

- [TUTORIAL_RAILWAY.md](TUTORIAL_RAILWAY.md) - Tutorial lengkap deploy ke Railway
- [railway-env-variables.json](railway-env-variables.json) - JSON file untuk import
- [railway.json](railway.json) - Railway configuration

---

**Need Help?**
- Lihat [TUTORIAL_RAILWAY.md](TUTORIAL_RAILWAY.md) untuk step-by-step guide
- Check Railway documentation: https://docs.railway.app

*Happy deploying!* ✨
