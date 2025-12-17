<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffKuaBanjarmasinUtaraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffData = [
            ['name' => 'H. BAITURRAHMAN, S.Ag', 'jabatan' => 'Kepala', 'nip' => '196907101997031007'],
            ['name' => 'JUNAIDI, S.Ag', 'jabatan' => 'Penghulu Ahli Muda', 'nip' => '197001022005011010'],
            ['name' => 'H. ABU ZAR AL GIFFARI, S.HI, M.Ag', 'jabatan' => 'Penghulu Ahli Muda', 'nip' => '198102042005011005'],
            ['name' => 'MUHAMMAD MAHDAN, S.HI', 'jabatan' => 'Penghulu Ahli Muda', 'nip' => '197609132009011007'],
            ['name' => 'Drs. H. HAJAJI, M.Pd.I', 'jabatan' => 'Penyuluh Agama Ahli Madya', 'nip' => '196707041995031001'],
            ['name' => 'HAFSAH, S.Ag', 'jabatan' => 'Penyuluh Agama Ahli Madya', 'nip' => '196805232006042006'],
            ['name' => 'HAIRUNNISA, S.Ag', 'jabatan' => 'Penyuluh Agama Ahli Muda', 'nip' => '197612102014112001'],
            ['name' => 'AHDADI, S.Ag', 'jabatan' => 'Penyuluh Agama Ahli Muda', 'nip' => '197108012014111003'],
            ['name' => 'FAHRUZAINI, S.Ag', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '197811182023211007'],
            ['name' => 'MAGFIROH, S.Ag', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '196911242023211003'],
            ['name' => 'SYAIFULAH, S.Sos. I', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '199002052023211017'],
            ['name' => 'ABU BAKAR, S.Ag', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '197604042023211008'],
            ['name' => 'ZAINUDDIN, S. Ag', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '197307082023211005'],
            ['name' => 'BAHRUL ILMI, Lc', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '199101012025211048'],
            ['name' => 'MUHAMMAD TASLIMURRAHMAN', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '198902252025211014'],
            ['name' => 'RATNA DEWI, S.H.I', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '198002112023212009'],
            ['name' => 'ZULFAKAR ALI, Lc', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '197806092025211008'],
            ['name' => 'MARLINA, S.Sos.I', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '198312042025212004'],
            ['name' => 'MUHAMMAD ABRAR MUTHOAKHIRU SHOHIBIE, S.Th.I', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '198509252025211016'],
            ['name' => 'TAUFIQURRAHMAN, S.Th.I', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '198601222025211007'],
            ['name' => 'MUHAMMAD TAQIYYUDDIN, Lc', 'jabatan' => 'Penyuluh Agama Ahli Pertama', 'nip' => '199407192025051003'],
            ['name' => 'SITI BULKIS, S.Ag', 'jabatan' => 'Penata Layanan Operasional', 'nip' => '196712151998032001'],
            ['name' => 'NURUL JANNAH S.Pd.I', 'jabatan' => 'Penata Layanan Operasional', 'nip' => '197009151990012001'],
            ['name' => 'FAHMI ZULKARNAIN, S.Ag.', 'jabatan' => 'Penata Layanan Operasional', 'nip' => '197506122009011015'],
            ['name' => 'HELYATI, A.Md', 'jabatan' => 'Pengolah Data dan Informasi', 'nip' => '197604212014112002'],
            ['name' => 'ARBAINAH, S.A.P.', 'jabatan' => 'Penata Layanan Operasional', 'nip' => '197206282014112002'],
            ['name' => 'KHAIRI FANANI, S.Kom', 'jabatan' => 'Penata Layanan Operasional', 'nip' => '199303032025211019'],
            ['name' => 'AHMAD NAWAWI, S. Pd.', 'jabatan' => 'Penata Layanan Operasional', 'nip' => '199304272025211014'],
            ['name' => 'MUSADDAD, S. Pd.I', 'jabatan' => 'Penata Layanan Operasional', 'nip' => '198807292025211012'],
            ['name' => 'MUHAMMAD RAIS MUCLISH, S.Pd.I', 'jabatan' => 'Penata Layanan Operasional', 'nip' => '199205252025211012'],
            ['name' => 'HERY HIDAYAT AR', 'jabatan' => 'Administrasi Perkantoran', 'nip' => '197905162025211008'],
            ['name' => 'FADLI, S. Pd', 'jabatan' => 'Penata Layanan Operasional', 'nip' => '198506222025211010'],
            ['name' => 'M. ZAINI, S. Pd.I', 'jabatan' => 'Penata Layanan Operasional', 'nip' => '198501252025211012'],
            ['name' => 'ABDUL HADI', 'jabatan' => 'Administrasi Perkantoran', 'nip' => '197803192025211024'],
            ['name' => 'JAILANI', 'jabatan' => 'Administrasi Perkantoran', 'nip' => '199302012025211042'],
        ];

        foreach ($staffData as $index => $staff) {
            
            // Tentukan role berdasarkan jabatan atau dari data jika sudah ditentukan
            $role = $staff['role'] ?? $this->determineRole($staff['jabatan']);
            
            // Generate email dari nama tanpa gelar (bersihkan karakter khusus)
            $namaTanpaGelar = $this->removeGelar($staff['name']);
            $emailBase = Str::slug(Str::lower($namaTanpaGelar), '');
            $emailBase = preg_replace('/[^a-z0-9]/', '', $emailBase);
            $email = $emailBase . '@kua-banjarutara.go.id';
            
            // Cek apakah email sudah ada, jika ya tambahkan nomor
            $counter = 1;
            $originalEmail = $email;
            while (User::where('email', $email)->exists()) {
                $email = $emailBase . $counter . '@kua-banjarutara.go.id';
                $counter++;
            }
            
            // Cek apakah user dengan NIP yang sama sudah ada
        if (User::where('nip', $staff['nip'])->exists()) {
            $this->command->warn("User dengan NIP {$staff['nip']} ({$staff['name']}) sudah ada. Dilewati.");
            continue;
        }
            
            // Buat user baru
            User::create([
                'name' => $staff['name'],
                'email' => $email,
                'password' => Hash::make('password123'), // Password default, bisa diubah nanti
                'nip' => $staff['nip'],
                'role' => $role,
                'jabatan' => $staff['jabatan'],
                'unit_kerja' => 'KUA Kecamatan Banjarmasin Utara',
                'is_active' => true,
            ]);
            
            $this->command->info("✓ Created user: {$staff['name']} ({$email}) - {$role}");
        }
        
        $this->command->info("\n✅ Selesai membuat akun staff KUA Banjarmasin Utara!");
    }
    
    /**
     * Tentukan role berdasarkan jabatan
     */
private function determineRole(string $jabatan): string
{
    $jabatanLower = Str::lower($jabatan); // <- INI WAJIB ADA

    if (Str::contains($jabatanLower, 'kepala')) {
        return 'kepala_kua';
    } elseif (Str::contains($jabatanLower, 'penghulu')) {
        return 'penghulu';
    } elseif (Str::contains($jabatanLower, 'penyuluh')) {
        return 'penyuluh_agama';
    }

    return 'pelaksana';
}

    
    /**
     * Hapus gelar dari nama (H., Hj., Dr., Drs., S.Ag, dll)
     */
    private function removeGelar(string $nama): string
    {
        // Daftar gelar yang perlu dihapus (urutkan dari yang paling spesifik ke yang umum)
        $gelar = [
            // Gelar depan (harus di awal)
            '/^H\.\s+/i',           // H.
            '/^Hj\.\s+/i',          // Hj.
            '/^Dr\.\s+/i',          // Dr.
            '/^Drs\.\s+/i',         // Drs.
            
            // Gelar belakang dengan koma (lebih spesifik dulu)
            '/\s*,\s*S\.Pd\.I\.?/i',        // , S.Pd.I atau , S.Pd.I.
            '/\s*,\s*S\.Sos\.I\.?/i',      // , S.Sos.I atau , S.Sos.I.
            '/\s*,\s*S\.H\.I\.?/i',        // , S.H.I atau , S.H.I.
            '/\s*,\s*S\.Th\.I\.?/i',       // , S.Th.I atau , S.Th.I.
            '/\s*,\s*M\.Pd\.I\.?/i',       // , M.Pd.I atau , M.Pd.I.
            '/\s*,\s*S\.A\.P\.?/i',        // , S.A.P atau , S.A.P.
            '/\s*,\s*S\.Ag\.?/i',          // , S.Ag atau , S.Ag.
            '/\s*,\s*S\.HI\.?/i',          // , S.HI atau , S.HI.
            '/\s*,\s*S\.Pd\.?/i',          // , S.Pd atau , S.Pd.
            '/\s*,\s*S\.Kom\.?/i',         // , S.Kom atau , S.Kom.
            '/\s*,\s*S\.E\.?/i',           // , S.E atau , S.E.
            '/\s*,\s*S\.Sos\.?/i',         // , S.Sos atau , S.Sos.
            '/\s*,\s*Lc\.?/i',             // , Lc atau , Lc.
            '/\s*,\s*M\.Pd\.?/i',          // , M.Pd atau , M.Pd.
            '/\s*,\s*M\.Ag\.?/i',          // , M.Ag atau , M.Ag.
            '/\s*,\s*M\.Si\.?/i',          // , M.Si atau , M.Si.
            '/\s*,\s*A\.Md\.?/i',          // , A.Md atau , A.Md.
            
            // Gelar belakang tanpa koma
            '/\s+S\.Pd\.I\.?/i',           // S.Pd.I atau S.Pd.I.
            '/\s+S\.Sos\.I\.?/i',          // S.Sos.I atau S.Sos.I.
            '/\s+S\.H\.I\.?/i',            // S.H.I atau S.H.I.
            '/\s+S\.Th\.I\.?/i',           // S.Th.I atau S.Th.I.
            '/\s+M\.Pd\.I\.?/i',           // M.Pd.I atau M.Pd.I.
            '/\s+S\.A\.P\.?/i',            // S.A.P atau S.A.P.
            '/\s+S\.Ag\.?/i',              // S.Ag atau S.Ag.
            '/\s+S\.HI\.?/i',              // S.HI atau S.HI.
            '/\s+S\.Pd\.?/i',              // S.Pd atau S.Pd.
            '/\s+S\.Kom\.?/i',             // S.Kom atau S.Kom.
            '/\s+S\.E\.?/i',               // S.E atau S.E.
            '/\s+S\.Sos\.?/i',             // S.Sos atau S.Sos.
            '/\s+Lc\.?/i',                 // Lc atau Lc.
            '/\s+M\.Pd\.?/i',              // M.Pd atau M.Pd.
            '/\s+M\.Ag\.?/i',              // M.Ag atau M.Ag.
            '/\s+M\.Si\.?/i',              // M.Si atau M.Si.
            '/\s+A\.Md\.?/i',              // A.Md atau A.Md.
        ];
        
        $namaBersih = $nama;
        
        // Hapus semua gelar
        foreach ($gelar as $pattern) {
            $namaBersih = preg_replace($pattern, '', $namaBersih);
        }
        
        // Bersihkan spasi ganda dan trim
        $namaBersih = preg_replace('/\s+/', ' ', trim($namaBersih));
        
        return $namaBersih;
    }
}
