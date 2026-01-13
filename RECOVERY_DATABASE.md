# 🚨 DATABASE RECOVERY GUIDE

## ⚠️ MASALAH: Database Hilang Karena Force Migrate

Database hilang karena di `.nixpacks.toml` ada perintah:
```bash
php artisan migrate --force && php artisan db:seed --force
```

Ini akan **DROP SEMUA TABLE** dan re-seed setiap kali deploy!

---

## ✅ SUDAH DIPERBAIKI

File `.nixpacks.toml` sudah diupdate menjadi **AMAN**:
```toml
cmd = "php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=$PORT"
```

**PERUBAHAN:**
- ❌ **DIHAPUS**: `php artisan db:seed --force` (bahaya!)
- ✅ **AMAN**: Hanya `migrate --force` yang run migrations baru saja

---

## 🔧 CARA RECOVERY DATABASE

### **1. Via Railway Console/Shell**

Login ke Railway Console dan jalankan:

```bash
# 1. Pastikan migrations sudah jalan
php artisan migrate:status

# 2. Run seeder untuk populate data staff
php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder

# 3. (Optional) Seed kategori kegiatan jika diperlukan
php artisan db:seed
```

### **2. Via Local + Push ke Production**

Jika ada backup data atau ingin manual input:

```bash
# Local: Generate SQL backup
php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder
# Manual export dari local database

# Copy SQL ke production via Railway Console
# Import via SQL query
```

---

## 📋 DATA YANG AKAN DI-SEED

**StaffKuaBanjarmasinUtaraSeeder** akan membuat:
- ✅ **1 Kepala KUA**: H. BAITURRAHMAN, S.Ag
- ✅ **32 Staff**: Penghulu, Penyuluh Agama, Pelaksana
- ✅ **Default Password**: `password` untuk semua user
- ✅ **Role Assignment**: Otomatis sesuai jabatan

---

## 🛡️ PENCEGAHAN DI MASA DEPAN

### **❌ JANGAN PERNAH**
```bash
php artisan migrate:fresh     # DROP semua table
php artisan migrate:reset     # DROP semua table  
php artisan migrate:rollback  # Rollback migrations
php artisan db:seed --force   # Di start command production
```

### **✅ AMAN UNTUK PRODUCTION**
```bash
php artisan migrate --force   # Run pending migrations saja
php artisan config:cache      # Cache config
php artisan route:cache       # Cache routes
php artisan view:cache        # Cache views
```

---

## 📊 CHECKLIST RECOVERY

- [ ] Push fix `.nixpacks.toml` ke Railway
- [ ] Wait for deployment
- [ ] Login ke Railway Console/Shell
- [ ] Run `php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder`
- [ ] Verify: Login dengan username staff dan password `password`
- [ ] (Optional) Seed kategori kegiatan kalau ada
- [ ] Test aplikasi untuk memastikan semua berjalan

---

## 🆘 JIKA MASIH ADA MASALAH

### Error: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Error: "Database doesn't exist"
Cek Railway > Settings > Variables:
- `DB_CONNECTION=mysql`
- `DB_HOST=...`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`

### Data Lama Hilang Permanen
Jika tidak ada backup, data LKH/SKP/Laporan yang lama **tidak bisa dikembalikan**.
Harus input ulang secara manual.

---

## 💾 REKOMENDASI: SETUP BACKUP OTOMATIS

### Via Railway Plugins
1. Add PostgreSQL/MySQL Backup Plugin
2. Set automatic daily backup
3. Retention: 7 days minimum

### Via Cron Job
Buat file: `app/Console/Commands/BackupDatabase.php`
```php
php artisan backup:run --only-db
```

Schedule di `app/Console/Kernel.php`:
```php
$schedule->command('backup:run --only-db')->daily();
```

---

**STATUS**: ✅ Fix applied, ready untuk re-deploy dengan aman!
