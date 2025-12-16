<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Lkh;
use App\Models\LaporanBulanan;
use App\Models\LaporanTriwulanan;
use App\Models\LaporanTahunan;
use App\Models\Skp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus semua data kecuali akun staff KUA Banjarmasin Utara';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('⚠️  PERINGATAN: Perintah ini akan menghapus semua data kecuali akun staff KUA Banjarmasin Utara!');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin melanjutkan?', false)) {
                $this->info('Operasi dibatalkan.');
                return 0;
            }
        }

        $this->info('Memulai proses cleanup...');
        $this->newLine();

        // Mulai transaction
        DB::beginTransaction();

        try {
            // Nonaktifkan foreign key checks sementara untuk memungkinkan truncate
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // 1. Hapus pivot tables terlebih dahulu
            $this->info('1. Menghapus pivot tables...');
            try {
                if (DB::getSchemaBuilder()->hasTable('laporan_bulanan_lkh')) {
                    DB::table('laporan_bulanan_lkh')->truncate();
                    $this->info("   ✓ Tabel laporan_bulanan_lkh dihapus");
                }
                if (DB::getSchemaBuilder()->hasTable('skp_laporan_bulanan')) {
                    DB::table('skp_laporan_bulanan')->truncate();
                    $this->info("   ✓ Tabel skp_laporan_bulanan dihapus");
                }
            } catch (\Exception $e) {
                $this->warn("   ⚠ Error menghapus pivot tables: " . $e->getMessage());
            }

            // 2. Hapus semua LKH
            $this->info('2. Menghapus semua LKH...');
            $lkhCount = Lkh::count();
            Lkh::truncate();
            $this->info("   ✓ {$lkhCount} LKH dihapus");

            // 3. Hapus semua Laporan Bulanan
            $this->info('3. Menghapus semua Laporan Bulanan...');
            $laporanBulananCount = LaporanBulanan::count();
            LaporanBulanan::truncate();
            $this->info("   ✓ {$laporanBulananCount} Laporan Bulanan dihapus");

            // 4. Hapus semua Laporan Triwulanan
            $this->info('4. Menghapus semua Laporan Triwulanan...');
            $laporanTriwulananCount = LaporanTriwulanan::count();
            LaporanTriwulanan::truncate();
            $this->info("   ✓ {$laporanTriwulananCount} Laporan Triwulanan dihapus");

            // 5. Hapus semua Laporan Tahunan
            $this->info('5. Menghapus semua Laporan Tahunan...');
            $laporanTahunanCount = LaporanTahunan::count();
            LaporanTahunan::truncate();
            $this->info("   ✓ {$laporanTahunanCount} Laporan Tahunan dihapus");

            // 6. Hapus semua SKP
            $this->info('6. Menghapus semua SKP...');
            $skpCount = Skp::count();
            Skp::truncate();
            $this->info("   ✓ {$skpCount} SKP dihapus");

            // 7. Hapus Ringkasan Harian (jika tabel ada)
            $this->info('7. Menghapus semua Ringkasan Harian...');
            try {
                if (DB::getSchemaBuilder()->hasTable('ringkasan_harian')) {
                    $ringkasanCount = DB::table('ringkasan_harian')->count();
                    DB::table('ringkasan_harian')->truncate();
                    $this->info("   ✓ {$ringkasanCount} Ringkasan Harian dihapus");
                } else {
                    $this->info("   ⚠ Tabel ringkasan_harian tidak ditemukan, dilewati");
                }
            } catch (\Exception $e) {
                $this->warn("   ⚠ Gagal menghapus ringkasan_harian: " . $e->getMessage());
            }

            // Aktifkan kembali foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // 8. Hapus semua user yang bukan staff KUA Banjarmasin Utara
            $this->info('8. Menghapus user yang bukan staff KUA Banjarmasin Utara...');
            $usersToDelete = User::where('unit_kerja', '!=', 'KUA Kecamatan Banjarmasin Utara')
                ->orWhereNull('unit_kerja')
                ->get();
            
            $deletedUsersCount = $usersToDelete->count();
            
            foreach ($usersToDelete as $user) {
                $this->line("   - Menghapus user: {$user->name} ({$user->email})");
                $user->delete();
            }
            
            $this->info("   ✓ {$deletedUsersCount} user dihapus");

            // 9. Tampilkan statistik user yang tersisa
            $this->newLine();
            $this->info('9. Statistik user yang tersisa:');
            $remainingUsers = User::where('unit_kerja', 'KUA Kecamatan Banjarmasin Utara')->get();
            
            $this->table(
                ['Nama', 'Email', 'Role', 'Jabatan'],
                $remainingUsers->map(function ($user) {
                    return [
                        $user->name,
                        $user->email,
                        ucfirst(str_replace('_', ' ', $user->role)),
                        $user->jabatan ?? '-',
                    ];
                })->toArray()
            );

            $this->info("   ✓ Total {$remainingUsers->count()} user staff KUA Banjarmasin Utara tersisa");

            // Commit transaction
            DB::commit();

            $this->newLine();
            $this->info('✅ Cleanup selesai! Semua data telah dihapus kecuali akun staff KUA Banjarmasin Utara.');
            
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Operasi dibatalkan. Tidak ada data yang dihapus.');
            return 1;
        }
    }
}
