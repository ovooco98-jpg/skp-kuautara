<div align="center">

# 📋 LKH KUA - Sistem Laporan Kegiatan Harian

**Modern web app untuk manage daily reports di KUA Banjarmasin Utara** ✨

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.13-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)](https://alpinejs.dev)

*No more paper trails, just digital vibes* 🚀

</div>

---

## 🎯 What's This About?

Aplikasi web modern buat ngelola Laporan Kegiatan Harian (LKH) di Kantor Urusan Agama. Say goodbye to paperwork yang ribet, hello to digital workflow yang smooth! 

Built with **Laravel 12** + **Tailwind CSS** + **Alpine.js** - basically the modern stack yang Gen Z developers love 💯

---

## ✨ Features That Hit Different

### 👤 For Staff/Pegawai
- ✅ Input LKH harian dengan kategori kegiatan (auto-suggest included!)
- ✅ View & filter semua LKH kamu
- ✅ Edit LKH yang masih draft/rejected
- ✅ Submit untuk approval (one-click, no hassle)
- ✅ Copy/duplicate dari hari sebelumnya (time-saver alert! ⏰)
- ✅ Upload & download lampiran (drag & drop ready)
- ✅ Auto-suggest kategori berdasarkan keyword (AI vibes 🤖)

### 👔 For Kepala KUA
- ✅ Dashboard dengan real-time stats (Total, Approved, Pending, Rejected)
- ✅ Approve/Reject LKH dengan catatan
- ✅ Monitor semua LKH pegawai (big brother mode 👀)
- ✅ Advanced filtering (pegawai, tanggal, status, bulan)
- ✅ Export Excel/CSV untuk laporan (spreadsheet gang 📊)
- ✅ Email notifications untuk LKH pending (never miss a thing 📧)

### 🔔 Notifications
- ✅ Email saat LKH di-submit (ke Kepala KUA)
- ✅ Email saat LKH di-approve (ke Pegawai)
- ✅ Email saat LKH di-reject (ke Pegawai)

*Basically, everyone stays in the loop* 🔄

---

## 🛠️ Tech Stack

| Category | Tech |
|----------|------|
| **Backend** | Laravel 12 (PHP 8.2+) |
| **Frontend** | Blade Templates + Alpine.js + Tailwind CSS 4.0 |
| **Database** | SQLite (default) / MySQL / PostgreSQL |
| **Charts** | Chart.js |
| **Build Tool** | Vite |

*Clean, modern, and maintainable* ✨

---

## 🚀 Quick Start (Let's Go!)

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- npm atau yarn

### Installation Steps

**1. Clone the repo**
```bash
git clone <repository-url>
cd lkh-kua
```

**2. Install dependencies**
```bash
# PHP packages
composer install

# JavaScript packages
npm install
```

**3. Setup environment**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Configure database**

Edit `.env` file:
```env
# Pilih salah satu:
DB_CONNECTION=sqlite  # Simplest option
# atau
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lkh_kua
DB_USERNAME=root
DB_PASSWORD=
```

**5. Setup database**
```bash
# Jika pakai SQLite
touch database/database.sqlite

# Run migrations
php artisan migrate
```

**6. Build assets**
```bash
# Development (with hot reload)
npm run dev

# Production
npm run build
```

**7. Start the server**
```bash
php artisan serve
```

**8. Open in browser**
```
http://localhost:8000
```

*That's it! You're ready to go* 🎉

---

## 📊 Database Structure

### Users
- `id`, `name`, `email`, `password`
- `nip`, `role`, `jabatan`, `unit_kerja`, `is_active`

### kategori_kegiatan
- `id`, `nama`, `deskripsi`, `role`, `is_active`

### lkh
- `id`, `user_id`, `tanggal`, `kategori_kegiatan_id`
- `uraian_kegiatan`, `waktu_mulai`, `waktu_selesai`
- `hasil_output`, `kendala`, `tindak_lanjut`, `lampiran`
- `status` (draft/submitted/approved/rejected)
- `approved_by`, `approved_at`, `catatan_approval`

---

## 👥 User Roles

| Role | Description |
|------|-------------|
| `kepala_kua` | Kepala KUA (can approve LKH) |
| `penghulu` | Penghulu |
| `penyuluh_agama` | Penyuluh Agama Islam |
| `pelaksana` | Pelaksana Tata Usaha |

---

## 🗺️ Routes Overview

### Public
- `/` → Redirect ke login/dashboard

### Authenticated
- `/dashboard` → Dashboard utama
- `/lkh` → Daftar LKH
- `/lkh/create` → Buat LKH baru
- `/lkh/{id}` → Detail LKH
- `/lkh/{id}/edit` → Edit LKH
- `/lkh/{id}/submit` → Submit LKH untuk approval

### Kepala KUA Only
- `/lkh/pending/approval` → List LKH pending
- `/lkh/{id}/approve` → Approve LKH
- `/lkh/{id}/reject` → Reject LKH

---

## 🎨 UI Components

Aplikasi pakai reusable Blade components (DRY principle, you know?):

- `<x-button>` → Button dengan berbagai variant
- `<x-card>` → Card container
- `<x-badge>` → Status badge
- `<x-modal>` → Modal dialog
- `<x-icon>` → Icon component
- `<x-layouts.app>` → Main layout

*Consistent UI = better UX* 🎯

---

## 💻 Development Tips

### Menambahkan User Baru

```bash
php artisan tinker
```

```php
User::create([
    'name' => 'Nama User',
    'email' => 'email@example.com',
    'password' => bcrypt('password'),
    'nip' => '123456789',
    'role' => 'penghulu',
    'jabatan' => 'Penghulu',
    'unit_kerja' => 'KUA Kecamatan Banjarmasin Utara',
]);
```

### Menambahkan Kategori Kegiatan

```php
KategoriKegiatan::create([
    'nama' => 'Pelayanan Nikah',
    'deskripsi' => 'Kegiatan pelayanan pernikahan',
    'role' => 'penghulu',
    'is_active' => true,
]);
```

---

## 🌐 Deployment (Production Ready!)

Aplikasi ini di-deploy menggunakan **Railway** - platform yang cocok untuk Laravel dengan MySQL di satu tempat.

### 🚂 Deploy ke Railway

**📖 Panduan lengkap:** Lihat [TUTORIAL_RAILWAY.md](TUTORIAL_RAILWAY.md) untuk step-by-step guide!

**Quick steps:**
1. Push code ke GitHub
2. Daftar di [railway.app](https://railway.app)
3. New Project → Deploy from GitHub repo
4. Add MySQL Database (di platform yang sama)
5. Setup environment variables (lihat [railway-env-variables.json](railway-env-variables.json))
6. Generate APP_KEY via Railway Shell
7. Run migrations: `php artisan migrate --force`
8. Seed data: `php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder`

### 📚 Deployment Guides

- **[TUTORIAL_RAILWAY.md](TUTORIAL_RAILWAY.md)** → Tutorial lengkap step-by-step deploy ke Railway (Laravel + MySQL) 🚂
  - **[railway-env-variables.json](railway-env-variables.json)** → Copy-paste ready environment variables 📋
  - **[railway-env-variables-template.md](railway-env-variables-template.md)** → Template dengan penjelasan
  - **[SETUP_CUSTOM_DOMAIN.md](SETUP_CUSTOM_DOMAIN.md)** → Setup custom domain dari Rumah Web 🌐 lengkap

---

## 🤝 Contributing

Want to contribute? That's awesome! Here's how:

1. Fork the repo
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

*Let's build something great together!* 🚀

---

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

*Free to use, free to modify* ✌️

---

## 🙏 Acknowledgments

- Built with ❤️ for KUA Banjarmasin Utara
- Powered by Laravel community
- Inspired by modern web development practices

---

<div align="center">

**Made with 💻 and ☕ by developers who care**

*Questions? Issues? Feel free to open an issue!*

</div>
