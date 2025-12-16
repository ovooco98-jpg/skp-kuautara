<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateEmailStaffCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staff:update-email {--force : Force update without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update email staff KUA agar tidak menyertakan gelar, hanya nama lengkap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memperbarui email staff KUA Banjarmasin Utara...');
        $this->info('Email akan diubah dari format dengan gelar menjadi format tanpa gelar (hanya nama lengkap)');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin melanjutkan?', false)) {
                $this->info('Operasi dibatalkan.');
                return 0;
            }
        }

        $staffUsers = User::where('unit_kerja', 'KUA Kecamatan Banjarmasin Utara')->get();
        
        if ($staffUsers->isEmpty()) {
            $this->warn('Tidak ada staff KUA Banjarmasin Utara ditemukan.');
            return 0;
        }

        $this->info("Ditemukan {$staffUsers->count()} staff KUA Banjarmasin Utara");
        $this->newLine();

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($staffUsers as $user) {
            // Hapus gelar dari nama
            $namaTanpaGelar = $this->removeGelar($user->name);
            
            // Generate email baru dari nama tanpa gelar
            $emailBase = Str::slug(Str::lower($namaTanpaGelar), '');
            $emailBase = preg_replace('/[^a-z0-9]/', '', $emailBase);
            $newEmail = $emailBase . '@kua-banjarutara.go.id';
            
            // Jika email sama dengan yang sekarang, skip
            if ($user->email === $newEmail) {
                $this->line("⏭  Skip: {$user->name} - Email sudah benar ({$user->email})");
                $skipped++;
                continue;
            }
            
            // Cek apakah email baru sudah digunakan oleh user lain
            $existingUser = User::where('email', $newEmail)
                ->where('id', '!=', $user->id)
                ->first();
            
            if ($existingUser) {
                // Jika email sudah digunakan, tambahkan nomor
                $counter = 1;
                $originalEmail = $newEmail;
                while (User::where('email', $newEmail)->where('id', '!=', $user->id)->exists()) {
                    $newEmail = $emailBase . $counter . '@kua-banjarutara.go.id';
                    $counter++;
                }
            }
            
            try {
                $oldEmail = $user->email;
                $user->email = $newEmail;
                $user->save();
                
                $this->info("✓ Updated: {$user->name}");
                $this->line("   {$oldEmail} → {$newEmail}");
                $updated++;
            } catch (\Exception $e) {
                $this->error("✗ Error: {$user->name} - {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info("✅ Selesai!");
        $this->info("   - Diperbarui: {$updated}");
        $this->info("   - Dilewati: {$skipped}");
        if ($errors > 0) {
            $this->warn("   - Error: {$errors}");
        }

        return 0;
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
