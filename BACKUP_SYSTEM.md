# 🔐 Sistem Backup Database - SKP KUA Banjarmasin Utara

## ⚠️ PELAJARAN PENTING

**Data LKH yang lama TIDAK BISA dikembalikan karena:**
- ❌ Tidak ada backup sebelum database di-reset
- ❌ Railway tidak menyimpan database backup otomatis
- ❌ `db:seed --force` menghapus semua data tanpa recovery

**Mulai sekarang, WAJIB backup berkala!**

---

## 📦 Sistem Backup Baru

Saya sudah buatkan 2 command artisan untuk backup otomatis:

### 1️⃣ **Backup Database**
```bash
# Backup semua table penting
php artisan backup:database

# Backup dengan compression (ukuran file lebih kecil)
php artisan backup:database --compress

# Backup table tertentu saja
php artisan backup:database --tables=lkh --tables=users
```

**File backup disimpan di:**
- Lokasi: `storage/app/backups/YYYY-MM/backup_YYYY-MM-DD_HHmmss.json`
- Format: JSON (human-readable)
- Auto cleanup: File lebih dari 30 hari otomatis dihapus

### 2️⃣ **Restore Database**
```bash
# List available backups
php artisan backup:restore "backups/2026-01/backup_2026-01-12_143000.json"

# Restore tanpa konfirmasi (hati-hati!)
php artisan backup:restore "backups/2026-01/backup_2026-01-12_143000.json" --force

# Restore table tertentu saja
php artisan backup:restore "backups/2026-01/backup_2026-01-12_143000.json" --tables=lkh
```

---

## 🗓️ Jadwal Backup yang Direkomendasikan

### **Backup Harian (Wajib!)**
Jalankan di Railway Console setiap hari:
```bash
php artisan backup:database --compress
```

### **Backup Sebelum Deploy**
Setiap kali mau deploy update, backup dulu:
```bash
# 1. Backup database
php artisan backup:database --compress

# 2. Download backup ke local
# Di Railway Dashboard → Data → Files
# Download file dari: /storage/app/backups/

# 3. Baru deploy update
```

### **Backup Sebelum Maintenance**
```bash
# 1. Enable maintenance mode
php artisan down --render="maintenance" --secret="recovery2026"

# 2. Backup database
php artisan backup:database --compress

# 3. Lakukan pekerjaan maintenance
# 4. Test aplikasi
# 5. Disable maintenance
php artisan up
```

---

## 📥 Download Backup dari Railway

### **Via Railway Dashboard:**
1. Buka project di Railway Dashboard
2. Klik tab **"Data"** atau **"File Browser"**
3. Navigate ke: `/storage/app/backups/`
4. Download file `.json` atau `.json.gz`
5. Simpan di komputer lokal (Google Drive, external drive, dll)

### **Via Railway CLI:**
```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Link project
railway link

# Download file
railway run cat storage/app/backups/2026-01/backup_2026-01-12_143000.json > backup-local.json
```

---

## 🔄 Cara Restore Jika Terjadi Disaster Lagi

### **Scenario: Database hilang lagi**
```bash
# 1. Login ke Railway Console

# 2. Upload backup file (via Railway dashboard file upload)
# Upload ke: /storage/app/backups/

# 3. List available backups
php artisan backup:restore "backups/2026-01/backup_2026-01-12_143000.json"

# 4. Restore dengan konfirmasi
php artisan backup:restore "backups/2026-01/backup_2026-01-12_143000.json"
# Ketik "yes" untuk konfirmasi

# 5. Verify data
php artisan tinker
>>> \App\Models\User::count()
>>> \App\Models\Lkh::count()
```

---

## 🤖 Setup Backup Otomatis (Optional - Butuh Cron/Task Scheduler)

Untuk backup otomatis setiap hari tanpa manual:

### **Option 1: Railway Cron Jobs (Berbayar)**
Railway Pro plan punya Cron Jobs feature

### **Option 2: External Scheduler**
Pakai service seperti:
- **cron-job.org** (Free)
- **EasyCron** (Free tier available)
- **GitHub Actions** (Free)

Setup endpoint untuk trigger backup:
```php
// routes/web.php
Route::post('/internal/backup', function () {
    // Protect dengan token
    if (request()->token !== env('BACKUP_SECRET_TOKEN')) {
        abort(403);
    }
    
    \Artisan::call('backup:database', ['--compress' => true]);
    return response()->json(['status' => 'success']);
});
```

---

## 📊 Isi Backup File

Setiap backup file berisi:
- ✅ **users** - Semua pegawai dan kepala KUA
- ✅ **lkh** - Semua laporan kegiatan harian
- ✅ **kategori_kegiatan** - Master kategori
- ✅ **laporan_bulanan** - Laporan bulanan
- ✅ **laporan_triwulanan** - Laporan triwulan
- ✅ **laporan_tahunan** - Laporan tahunan
- ✅ **skp** - Data SKP pegawai
- ✅ **ringkasan_harian** - Ringkasan harian

Plus metadata:
- Tanggal backup
- Total table & records
- Versi aplikasi
- Versi Laravel

---

## ⚡ Quick Commands

```bash
# Backup sekarang juga!
php artisan backup:database --compress

# List semua backups
ls -lh storage/app/backups/

# Check ukuran backup
du -sh storage/app/backups/

# Restore backup terakhir
php artisan backup:restore "backups/2026-01/backup_LATEST.json"
```

---

## 🚨 REMINDER PENTING

1. **BACKUP SEBELUM DEPLOY** - WAJIB!
2. **Download backup ke local** - Jangan cuma di server
3. **Test restore** - Minimal 1x sebulan test restore backup
4. **Never use `db:seed --force` in production** - JANGAN PERNAH!
5. **Simpan backup di multiple lokasi** - Server + Local + Cloud Storage

---

## 📞 Troubleshooting

### **Error: Permission denied**
```bash
# Fix permission
chmod -R 775 storage/app/backups
chown -R www-data:www-data storage/app/backups
```

### **Error: Disk full**
```bash
# Check disk space
df -h

# Manual cleanup old backups
php artisan backup:database  # Auto cleanup dijalankan
```

### **Backup file corrupt**
```bash
# Verify JSON file
cat storage/app/backups/file.json | jq .

# Decompress .gz file
gunzip -c storage/app/backups/file.json.gz | jq .
```

---

## ✅ Action Items - LAKUKAN SEKARANG

- [ ] Test backup command: `php artisan backup:database`
- [ ] Download backup ke local computer
- [ ] Upload backup ke Google Drive/Dropbox
- [ ] Set reminder di HP untuk backup setiap hari
- [ ] Test restore dengan backup test
- [ ] Deploy command baru ke Railway (git push)

---

**Dibuat untuk mencegah disaster data hilang lagi!**
**Astagfirullah, jangan sampai terulang! 🤲**
