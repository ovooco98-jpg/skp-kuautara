# 🌐 Setup Custom Domain di Railway

*Panduan lengkap untuk setup custom domain dari Rumah Web ke Railway* 🎯

---

## 📋 Prerequisites

- ✅ Akun Railway dengan service yang sudah deployed
- ✅ Custom domain dari Rumah Web (atau provider domain lain)
- ✅ Akses ke DNS management di Rumah Web

---

## 🚀 Step 1: Setup Domain di Railway

### 1.1 Buka Railway Dashboard

1. Login ke [Railway Dashboard](https://railway.app)
2. Pilih project yang mau di-setup domain
3. Click service yang mau di-attach domain (contoh: `lkh-kua`)

### 1.2 Add Custom Domain

1. Di service dashboard, click tab **"Settings"**
2. Scroll ke bagian **"Networking"** atau **"Custom Domains"**
3. Click **"Generate Domain"** (untuk Railway domain) atau **"Add Custom Domain"**
4. Masukkan domain kamu:
   - Contoh: `app.yourdomain.com` atau `lkh-kua.yourdomain.com`
   - Atau root domain: `yourdomain.com`
5. Click **"Add"** atau **"Save"**

### 1.3 Get DNS Records

Setelah add domain, Railway akan generate DNS records yang perlu di-setup:

**Contoh DNS records dari Railway:**
```
Type: CNAME
Name: app (atau @ untuk root domain)
Value: xxxxx.up.railway.app
TTL: 3600
```

Atau untuk root domain:
```
Type: A
Name: @
Value: 76.76.21.21 (IP Railway)
TTL: 3600
```

**💡 Tips:**
- Railway akan provide exact DNS records yang perlu di-setup
- Copy semua records yang diberikan Railway
- Untuk root domain, biasanya pakai A record
- Untuk subdomain, biasanya pakai CNAME record

---

## 🔧 Step 2: Setup DNS di Rumah Web

### 2.1 Login ke Rumah Web

1. Buka [Rumah Web](https://rumahweb.com) atau portal domain management kamu
2. Login ke akun
3. Pilih domain yang mau di-setup

### 2.2 Buka DNS Management

1. Di dashboard domain, cari menu **"DNS Management"** atau **"Zone Editor"**
2. Atau cari **"Manage DNS"** / **"DNS Settings"**
3. Buka halaman DNS management

### 2.3 Add DNS Records

**Untuk Subdomain (contoh: app.yourdomain.com):**

1. Click **"Add Record"** atau **"Tambah Record"**
2. Isi form:
   - **Type**: `CNAME`
   - **Name/Host**: `app` (atau subdomain yang kamu mau)
   - **Value/Target**: `xxxxx.up.railway.app` (dari Railway)
   - **TTL**: `3600` (atau default)
3. Click **"Save"** atau **"Simpan"**

**Untuk Root Domain (contoh: yourdomain.com):**

1. Click **"Add Record"** atau **"Tambah Record"**
2. Isi form:
   - **Type**: `A`
   - **Name/Host**: `@` (atau kosongkan untuk root)
   - **Value/IP**: `76.76.21.21` (IP Railway - cek di Railway dashboard)
   - **TTL**: `3600` (atau default)
3. Click **"Save"** atau **"Simpan"**

**💡 Tips:**
- Untuk root domain, Railway biasanya provide IP address
- Untuk subdomain, pakai CNAME ke Railway domain
- TTL bisa pakai default (biasanya 3600)

### 2.4 Verify DNS Records

Setelah add records, verify:
- CNAME record untuk subdomain mengarah ke Railway domain
- A record untuk root domain mengarah ke Railway IP
- TTL sudah di-set (biasanya 3600)

---

## ⏱️ Step 3: Wait for DNS Propagation

### 3.1 DNS Propagation Time

- **Biasanya**: 5-15 menit
- **Maksimal**: 24-48 jam (tapi jarang)
- **TTL**: Mempengaruhi propagation time

### 3.2 Check DNS Propagation

**Via Command Line:**
```bash
# Windows (PowerShell)
nslookup app.yourdomain.com

# Mac/Linux
dig app.yourdomain.com
```

**Via Online Tools:**
- [whatsmydns.net](https://www.whatsmydns.net)
- [dnschecker.org](https://dnschecker.org)

**Expected Result:**
- CNAME: Should point to `xxxxx.up.railway.app`
- A Record: Should point to Railway IP

---

## ✅ Step 4: Verify di Railway

### 4.1 Check Domain Status

1. Kembali ke Railway Dashboard → Service → Settings
2. Di bagian **"Custom Domains"**, cek status domain:
   - **Pending**: DNS masih propagate
   - **Active**: Domain sudah aktif dan SSL sudah di-generate
   - **Error**: Ada masalah dengan DNS setup

### 4.2 SSL Certificate

Railway akan otomatis generate SSL certificate (Let's Encrypt):
- **Automatic**: Railway auto-generate SSL setelah DNS verified
- **Time**: Biasanya 5-10 menit setelah DNS active
- **Status**: Akan muncul di Railway dashboard

**💡 Tips:**
- Railway menggunakan Let's Encrypt untuk SSL gratis
- SSL akan auto-renew
- Tidak perlu setup manual

---

## 🔧 Step 5: Update Environment Variables

### 5.1 Update APP_URL

1. Di Railway Dashboard → Service → Variables
2. Cari variable `APP_URL`
3. Update value:
   - **Sebelum**: `https://lkh-kua-production.up.railway.app`
   - **Sesudah**: `https://app.yourdomain.com` (atau domain kamu)
4. Click **"Save"** atau **"Update"**

### 5.2 Redeploy (Jika Perlu)

Setelah update `APP_URL`, Railway akan otomatis redeploy:
- Tunggu beberapa menit
- Atau trigger manual redeploy di Railway dashboard

---

## 🧪 Step 6: Test Domain

### 6.1 Test di Browser

1. Buka browser
2. Akses domain kamu: `https://app.yourdomain.com`
3. Cek apakah aplikasi load dengan benar
4. Cek SSL certificate (harus ada lock icon di browser)

### 6.2 Test Features

1. Test login
2. Test semua features aplikasi
3. Pastikan semua URL menggunakan custom domain

---

## 🐛 Troubleshooting

### Problem: Domain Status "Pending" atau "Error"

**Solusi:**
1. Cek DNS records di Rumah Web:
   - Pastikan CNAME/A record sudah benar
   - Pastikan value mengarah ke Railway domain/IP
2. Wait untuk DNS propagation (bisa sampai 24 jam)
3. Check DNS propagation via online tools
4. Verify di Railway dashboard apakah records sudah correct

### Problem: SSL Certificate Tidak Generate

**Solusi:**
1. Pastikan DNS sudah propagate (cek via nslookup/dig)
2. Pastikan domain sudah active di Railway
3. Tunggu beberapa menit (SSL generation bisa sampai 10 menit)
4. Jika masih error, coba remove dan add domain lagi di Railway

### Problem: Domain Tidak Load

**Solusi:**
1. Cek DNS propagation:
   ```bash
   nslookup app.yourdomain.com
   ```
2. Cek apakah mengarah ke Railway:
   - CNAME harus point ke `xxxxx.up.railway.app`
   - A record harus point ke Railway IP
3. Clear browser cache
4. Cek Railway logs untuk errors

### Problem: Mixed Content (HTTP/HTTPS)

**Solusi:**
1. Pastikan `APP_URL` di environment variables menggunakan `https://`
2. Pastikan semua assets menggunakan HTTPS
3. Check browser console untuk mixed content warnings

### Problem: Subdomain Tidak Bekerja

**Solusi:**
1. Pastikan CNAME record sudah benar:
   - Name: `app` (atau subdomain kamu)
   - Value: Railway domain
2. Pastikan tidak ada conflict dengan A record
3. Wait untuk DNS propagation

---

## 📝 Checklist Setup

- [ ] Domain sudah di-add di Railway dashboard
- [ ] DNS records sudah di-copy dari Railway
- [ ] CNAME/A record sudah di-setup di Rumah Web
- [ ] DNS sudah propagate (cek via nslookup/dig)
- [ ] Domain status di Railway sudah "Active"
- [ ] SSL certificate sudah di-generate
- [ ] APP_URL sudah di-update di environment variables
- [ ] Domain bisa diakses di browser
- [ ] SSL certificate valid (lock icon di browser)
- [ ] Semua features aplikasi berfungsi dengan benar

---

## 💡 Tips & Best Practices

### 1. Subdomain vs Root Domain

**Subdomain (Recommended):**
- ✅ Lebih mudah setup (pakai CNAME)
- ✅ Bisa pakai untuk multiple services
- ✅ Contoh: `app.yourdomain.com`, `api.yourdomain.com`

**Root Domain:**
- ⚠️ Perlu A record (lebih kompleks)
- ⚠️ Tidak bisa pakai untuk multiple services
- ⚠️ Contoh: `yourdomain.com`

### 2. Multiple Services

Jika punya multiple services di Railway:
- Setup subdomain untuk setiap service
- Contoh: `app.yourdomain.com`, `api.yourdomain.com`
- Setiap service punya CNAME record sendiri

### 3. DNS TTL

- **Default**: 3600 (1 jam)
- **Lower TTL**: Untuk testing (300 = 5 menit)
- **Higher TTL**: Untuk production (86400 = 24 jam)

### 4. SSL Certificate

- Railway auto-generate SSL via Let's Encrypt
- SSL auto-renew setiap 90 hari
- Tidak perlu setup manual
- Support wildcard domain (jika perlu)

---

## 📚 Additional Resources

- [Railway Custom Domains Documentation](https://docs.railway.app/deploy/custom-domains)
- [Rumah Web DNS Management Guide](https://www.rumahweb.com/knowledge-base/)
- [DNS Propagation Checker](https://www.whatsmydns.net)

---

## 🎉 Selamat!

Custom domain kamu sekarang sudah terhubung ke Railway! 🚀

**Domain:** `https://app.yourdomain.com` (atau domain kamu)

**Next Steps:**
1. Share domain dengan team
2. Update documentation dengan custom domain
3. Setup monitoring untuk domain
4. Enjoy your custom domain! 🎊

---

**Need Help?**
- Check Railway logs untuk errors
- Check DNS propagation status
- Contact Railway support jika ada masalah
- Check Rumah Web support untuk DNS issues

*Happy deploying!* ✨
