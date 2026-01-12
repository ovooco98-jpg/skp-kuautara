# 🔧 MAINTENANCE MODE - Environment Variable

## ⚡ CARA CEPAT ENABLE/DISABLE

### **Step 1: Buka Railway Dashboard**
1. Login ke https://railway.app
2. Pilih project: **SKP KUA Banjarmasin Utara**
3. Klik service/deployment kamu

### **Step 2: Tambah Environment Variable**
1. Klik tab **"Variables"** atau **"Settings"**
2. Klik **"+ New Variable"** atau **"Add Variable"**
3. Isi:
   - **Variable:** `APP_MAINTENANCE_MODE`
   - **Value:** `true`
4. Klik **"Save"** atau **"Add"**

### **Step 3: Railway Auto-Redeploy**
Railway akan otomatis redeploy (~2-3 menit)

### **Step 4: Verify**
Buka: https://www.kua-banjarmasin-utara.my.id
Seharusnya muncul halaman maintenance! ✅

---

## 🔓 BYPASS UNTUK ADMIN

Akses URL ini untuk set cookie bypass:
```
https://www.kua-banjarmasin-utara.my.id/recovery2026
```

Setelah akses URL ini, kamu akan di-redirect ke homepage dan bisa akses normal (cookie berlaku 7 hari).

---

## ✅ DISABLE MAINTENANCE MODE (Setelah Recovery Selesai)

### **Cara 1: Hapus Variable** (RECOMMENDED)
1. Buka Railway Dashboard → Variables
2. Cari variable `APP_MAINTENANCE_MODE`
3. Klik **"Delete"** atau icon hapus
4. Save & tunggu redeploy

### **Cara 2: Ubah Value ke False**
1. Edit variable `APP_MAINTENANCE_MODE`
2. Ubah value jadi: `false`
3. Save & tunggu redeploy

---

## 🎯 CUSTOM SECRET BYPASS (Optional)

Kalau mau ganti secret bypass dari "recovery2026" ke yang lain:

1. Tambah variable di Railway:
   - **Variable:** `MAINTENANCE_SECRET`
   - **Value:** `rahasia-admin-2026` (ganti sesuai keinginan)

2. Bypass URL jadi:
   ```
   https://www.kua-banjarmasin-utara.my.id/rahasia-admin-2026
   ```

---

## 📋 CHECKLIST DEPLOYMENT

- [ ] Push code baru ke Railway (dengan middleware)
- [ ] Tunggu deploy selesai
- [ ] Tambah env variable `APP_MAINTENANCE_MODE=true`
- [ ] Tunggu redeploy selesai
- [ ] Verify maintenance page muncul
- [ ] Test bypass URL untuk admin
- [ ] Jalankan backup: `railway run php artisan backup:database --compress`
- [ ] Restore database (kalau perlu)
- [ ] Test aplikasi dengan bypass URL
- [ ] Disable maintenance: Hapus variable atau set `false`
- [ ] Verify aplikasi normal untuk semua user

---

## 🚨 TROUBLESHOOTING

### **Maintenance tidak muncul**
- Cek variable name exactly: `APP_MAINTENANCE_MODE` (case-sensitive)
- Value harus: `true` (lowercase)
- Tunggu redeploy selesai (cek Deployment logs)

### **Bypass tidak work**
- Clear browser cookies
- Akses URL bypass lagi
- Cek console browser untuk error

### **Error 500 setelah enable**
- Cek Railway logs: Dashboard → View Logs
- Kemungkinan maintenance.blade.php belum ter-push
- Push semua file dulu, baru enable maintenance

---

## 📦 NEXT: DEPLOY SEKARANG

Jalankan script untuk deploy:
```bash
deploy-backup-system.bat
```

Atau manual:
```bash
git add .
git commit -m "Add: Maintenance mode via ENV variable"
git push origin main
```

Tunggu Railway deploy selesai, baru tambah env variable!
