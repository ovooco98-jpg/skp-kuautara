# 🔧 MAINTENANCE MODE GUIDE

## 🚀 CARA ENABLE MAINTENANCE MODE

### **1. Via Railway Console/Shell** (RECOMMENDED)

Login ke Railway Console dan jalankan:

```bash
# Enable maintenance mode
php artisan down --render="maintenance" --refresh=15

# Dengan secret bypass untuk admin
php artisan down --render="maintenance" --secret="recovery2026" --refresh=15
```

**Penjelasan:**
- `--render="maintenance"` → Gunakan custom view yang sudah dibuat
- `--secret="recovery2026"` → Admin bisa akses dengan URL: `https://your-app.com/recovery2026`
- `--refresh=15` → Auto refresh page setiap 15 detik

### **2. Disable Maintenance Mode** (Setelah Recovery Selesai)

```bash
php artisan up
```

---

## 🔐 BYPASS MAINTENANCE MODE (ADMIN ONLY)

Setelah enable dengan secret, admin bisa akses dengan:

```
https://www.kua-banjarmasin-utara.my.id/recovery2026
```

Browser akan set cookie dan admin bisa akses normal tanpa halaman maintenance.

---

## 🎨 CUSTOM MAINTENANCE PAGE

File yang dibuat: `resources/views/maintenance.blade.php`

**Fitur:**
- ✅ Tampilan modern & responsive
- ✅ Auto refresh setiap 5 menit
- ✅ Countdown timer 30 menit
- ✅ Info kontak untuk bantuan
- ✅ Professional design dengan Tailwind CSS

---

## 📋 WORKFLOW LENGKAP MAINTENANCE

### **Step 1: Enable Maintenance**
```bash
# Di Railway Console
php artisan down --render="maintenance" --secret="recovery2026" --refresh=15
```

### **Step 2: Lakukan Recovery Database**
```bash
# Seed staff data
php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder

# Verify
php artisan db:seed --class=DatabaseSeeder
```

### **Step 3: Test Aplikasi**
```bash
# Akses via secret URL
https://www.kua-banjarmasin-utara.my.id/recovery2026

# Test login, fitur, dll
```

### **Step 4: Disable Maintenance**
```bash
# Di Railway Console
php artisan up
```

### **Step 5: Verify Public Access**
```bash
# Buka browser incognito/private
https://www.kua-banjarmasin-utara.my.id
```

---

## 🛡️ ALTERNATIVE: Middleware Custom

Jika tidak mau gunakan `php artisan down`, bisa pakai middleware custom.

### **Option A: Environment Variable**

Tambah di Railway Variables:
```env
MAINTENANCE_MODE=true
```

Lalu di `routes/web.php`:
```php
Route::middleware(['web', function ($request, $next) {
    if (env('MAINTENANCE_MODE', false)) {
        return response()->view('maintenance', [], 503);
    }
    return $next($request);
}])->group(function () {
    // All your routes here
});
```

### **Option B: Laravel Config**

Tambah di `config/app.php`:
```php
'maintenance' => env('MAINTENANCE_MODE', false),
```

Gunakan middleware yang sama seperti Option A.

---

## ⚠️ IMPORTANT NOTES

1. **Jangan lupa disable** setelah maintenance selesai!
2. **Secret URL** jangan share ke public
3. **Test dulu** di staging/local sebelum enable di production
4. **Backup** secret URL untuk akses admin
5. **Communicate** ke user via email/WhatsApp Group tentang maintenance schedule

---

## 🔍 TROUBLESHOOTING

### Error: "View not found"
```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

### Maintenance mode tidak muncul
```bash
# Check status
php artisan down

# Clear all cache
php artisan optimize:clear

# Re-enable
php artisan up
php artisan down --render="maintenance" --secret="recovery2026"
```

### Cannot access via secret URL
1. Clear browser cache & cookies
2. Try incognito/private window
3. Check Railway logs untuk error
4. Pastikan secret string benar

---

## 📱 NOTIFICATION TEMPLATE

Kirim ke WhatsApp Group/Email staff:

```
🔧 PEMBERITAHUAN MAINTENANCE SISTEM

Kepada Yth. Bapak/Ibu Staff KUA Banjarmasin Utara,

Sistem LKH akan mengalami maintenance pada:
📅 Hari ini, {{ date }} 
⏰ Pukul {{ time }} WITA
⏱️ Durasi: ±30 menit

Selama maintenance, sistem tidak dapat diakses.
Mohon untuk tidak melakukan input data sementara waktu.

Terima kasih atas pengertiannya.

---
Tim IT KUA Banjarmasin Utara
```

---

**STATUS**: ✅ Maintenance page ready, tinggal enable via Railway Console!
