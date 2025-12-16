# LKH KUA - Sistem Laporan Kegiatan Harian

Aplikasi web untuk mengelola Laporan Kegiatan Harian (LKH) di Kantor Urusan Agama (KUA) Banjarmasin Utara.

## Teknologi yang Digunakan

- **Backend**: Laravel 12
- **Frontend**: Blade Templates + Alpine.js + Tailwind CSS
- **Database**: SQLite (default) / MySQL / PostgreSQL
- **JavaScript**: Alpine.js untuk interaktivitas, Chart.js untuk visualisasi

## Fitur MVP

### Untuk Pegawai
- ✅ Input LKH harian dengan kategori kegiatan
- ✅ Lihat daftar LKH dengan filter
- ✅ Detail LKH lengkap
- ✅ Edit LKH (draft/rejected)
- ✅ Submit LKH untuk approval
- ✅ Copy/Duplicate LKH dari hari sebelumnya
- ✅ Upload & download lampiran
- ✅ Auto-suggest kategori berdasarkan keyword

### Untuk Kepala KUA
- ✅ Dashboard dengan statistik (Total, Approved, Pending, Rejected)
- ✅ Approval/Reject LKH dengan catatan
- ✅ Monitoring semua LKH pegawai
- ✅ Filter berdasarkan pegawai, tanggal, status, bulan
- ✅ Export Excel/CSV untuk laporan
- ✅ Email notification untuk LKH pending

### Notification
- ✅ Email saat LKH di-submit (ke Kepala KUA)
- ✅ Email saat LKH di-approve (ke Pegawai)
- ✅ Email saat LKH di-reject (ke Pegawai)

## Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd lkh-kua
```

### 2. Install Dependencies

**PHP Dependencies:**
```bash
composer install
```

**JavaScript Dependencies:**
```bash
npm install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file untuk konfigurasi database:
```env
DB_CONNECTION=sqlite
# atau
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lkh_kua
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Setup Database
```bash
# Jika menggunakan SQLite
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### 5. Build Assets
```bash
# Development mode (with hot reload)
npm run dev

# Production build
npm run build
```

### 6. Start Development Server
```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

## Struktur Database

### Users
- id, name, email, password
- nip, role, jabatan, unit_kerja, is_active

### kategori_kegiatan
- id, nama, deskripsi, role, is_active

### lkh
- id, user_id, tanggal, kategori_kegiatan_id
- uraian_kegiatan, waktu_mulai, waktu_selesai
- hasil_output, kendala, tindak_lanjut, lampiran
- status (draft/submitted/approved/rejected)
- approved_by, approved_at, catatan_approval

## Roles

- `kepala_kua` - Kepala KUA (bisa approve LKH)
- `penghulu` - Penghulu
- `penyuluh_agama` - Penyuluh Agama Islam
- `pelaksana` - Pelaksana Tata Usaha

## Routes

### Public
- `/` - Redirect ke login/dashboard

### Authenticated
- `/dashboard` - Dashboard
- `/lkh` - Daftar LKH
- `/lkh/create` - Buat LKH baru
- `/lkh/{id}` - Detail LKH
- `/lkh/{id}/edit` - Edit LKH
- `/lkh/{id}/submit` - Submit LKH untuk approval

### Kepala KUA Only
- `/lkh/pending/approval` - List LKH pending
- `/lkh/{id}/approve` - Approve LKH
- `/lkh/{id}/reject` - Reject LKH

## Komponen UI

Aplikasi menggunakan komponen Blade yang reusable:

- `<x-button>` - Button dengan berbagai variant
- `<x-card>` - Card container
- `<x-badge>` - Status badge
- `<x-modal>` - Modal dialog
- `<x-icon>` - Icon component
- `<x-layouts.app>` - Main layout

## Development

### Menambahkan User Baru (Seeder)

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

## 🚀 Deployment

Aplikasi ini dapat di-deploy ke berbagai platform. **Rekomendasi: Railway** untuk deployment Laravel + MySQL.

### Platform yang Didukung:

1. **Railway (Rekomendasi)** ⭐
   - ✅ Laravel + MySQL di satu platform
   - ✅ Auto-deploy dari GitHub
   - ✅ Free tier tersedia
   - 📖 Lihat panduan lengkap di [DEPLOYMENT.md](DEPLOYMENT.md)

2. **Vercel** (Tidak Direkomendasikan)
   - ⚠️ Vercel tidak ideal untuk Laravel
   - Perlu banyak penyesuaian
   - 📖 Lihat [DEPLOYMENT.md](DEPLOYMENT.md) untuk detail

3. **Alternatif Lain:**
   - Laravel Forge
   - DigitalOcean App Platform
   - AWS Elastic Beanstalk
   - Heroku (dengan addon MySQL)

### Quick Start Deployment ke Railway:

1. Push code ke GitHub
2. Daftar di [railway.app](https://railway.app)
3. Buat project baru → Deploy from GitHub
4. Tambahkan MySQL service
5. Setup environment variables (lihat [DEPLOYMENT.md](DEPLOYMENT.md))
6. Run migrations: `php artisan migrate --force`
7. Seed data: `php artisan db:seed --class=StaffKuaBanjarmasinUtaraSeeder`

**📚 Panduan lengkap:** Lihat [DEPLOYMENT.md](DEPLOYMENT.md) untuk instruksi detail.

## License

MIT License
