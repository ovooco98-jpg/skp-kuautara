<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\KategoriKegiatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Users
        $this->seedUsers();
        
        // Seed Kategori Kegiatan
        $this->seedKategoriKegiatan();
        
        // Uncomment baris di bawah untuk seed data presentasi (banyak data)
        // $this->call(PresentationSeeder::class);
    }


   function seedKategoriKegiatan(): void
    {
        // Kategori untuk Penghulu
        KategoriKegiatan::create([
            'nama' => 'Pelayanan Pendaftaran Nikah',
            'deskripsi' => 'Pelayanan pendaftaran pernikahan',
            'role' => 'penghulu',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Pelaksanaan Akad Nikah',
            'deskripsi' => 'Pelaksanaan akad nikah',
            'role' => 'penghulu',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Bimbingan Pra Nikah',
            'deskripsi' => 'Memberikan bimbingan kepada calon pengantin',
            'role' => 'penghulu',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Pencatatan dan Pelaporan Nikah',
            'deskripsi' => 'Pencatatan dan pelaporan data pernikahan',
            'role' => 'penghulu',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Pelayanan Rujuk',
            'deskripsi' => 'Pelayanan rujuk',
            'role' => 'penghulu',
            'is_active' => true,
        ]);

        // Kategori untuk Penyuluh Agama
        KategoriKegiatan::create([
            'nama' => 'Bimbingan Keluarga Sakinah',
            'deskripsi' => 'Memberikan bimbingan untuk keluarga sakinah',
            'role' => 'penyuluh_agama',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Bimbingan Kemasjidan',
            'deskripsi' => 'Bimbingan pengelolaan masjid',
            'role' => 'penyuluh_agama',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Bimbingan Zakat dan Wakaf',
            'deskripsi' => 'Bimbingan tentang zakat dan wakaf',
            'role' => 'penyuluh_agama',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Penyuluhan Keagamaan',
            'deskripsi' => 'Penyuluhan dan ceramah keagamaan',
            'role' => 'penyuluh_agama',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Bimbingan Hisab Rukyat',
            'deskripsi' => 'Bimbingan hisab rukyat untuk penentuan waktu ibadah',
            'role' => 'penyuluh_agama',
            'is_active' => true,
        ]);

        // Kategori untuk Pelaksana
        KategoriKegiatan::create([
            'nama' => 'Pengarsipan Dokumen',
            'deskripsi' => 'Pengarsipan dokumen pernikahan dan lainnya',
            'role' => 'pelaksana',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Input Data Sistem Informasi',
            'deskripsi' => 'Input data ke sistem informasi manajemen KUA',
            'role' => 'pelaksana',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Penyusunan Statistik Layanan',
            'deskripsi' => 'Penyusunan statistik layanan KUA',
            'role' => 'pelaksana',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Administrasi dan Ketatausahaan',
            'deskripsi' => 'Kegiatan administrasi dan ketatausahaan',
            'role' => 'pelaksana',
            'is_active' => true,
        ]);

        // Kategori untuk semua (umum)
        KategoriKegiatan::create([
            'nama' => 'Rapat Koordinasi',
            'deskripsi' => 'Rapat koordinasi internal',
            'role' => 'semua',
            'is_active' => true,
        ]);

        KategoriKegiatan::create([
            'nama' => 'Pelatihan dan Pengembangan',
            'deskripsi' => 'Kegiatan pelatihan dan pengembangan',
            'role' => 'semua',
            'is_active' => true,
        ]);
    }
}
