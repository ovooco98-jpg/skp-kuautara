<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database 
                            {--tables=* : Specific tables to backup}
                            {--compress : Compress backup file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database tables to JSON format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting database backup...');

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $backupDir = 'backups/' . Carbon::now()->format('Y-m');
        
        // Pastikan direktori backup ada
        if (!Storage::exists($backupDir)) {
            Storage::makeDirectory($backupDir);
        }

        // Tables yang akan di-backup (penting!)
        $tables = $this->option('tables') ?: [
            'users',
            'lkh',
            'kategori_kegiatan',
            'laporan_bulanan',
            'laporan_triwulanan',
            'laporan_tahunan',
            'skp',
            'ringkasan_harian'
        ];

        $backupData = [];
        $totalRecords = 0;

        foreach ($tables as $table) {
            try {
                $this->info("📦 Backing up table: {$table}");
                
                // Ambil semua data dari table
                $data = DB::table($table)->get()->toArray();
                $count = count($data);
                
                $backupData[$table] = [
                    'count' => $count,
                    'data' => $data
                ];
                
                $totalRecords += $count;
                $this->line("   ✓ {$count} records backed up");
                
            } catch (\Exception $e) {
                $this->error("   ✗ Failed to backup {$table}: " . $e->getMessage());
            }
        }

        // Simpan metadata
        $backupData['_metadata'] = [
            'backup_date' => Carbon::now()->toDateTimeString(),
            'total_tables' => count($tables),
            'total_records' => $totalRecords,
            'app_version' => config('app.version', '1.0.0'),
            'laravel_version' => app()->version(),
        ];

        // Save backup file
        $filename = "backup_{$timestamp}.json";
        $filepath = $backupDir . '/' . $filename;
        
        $jsonContent = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Compress jika diminta
        if ($this->option('compress')) {
            $jsonContent = gzcompress($jsonContent, 9);
            $filename = "backup_{$timestamp}.json.gz";
            $filepath = $backupDir . '/' . $filename;
        }
        
        Storage::put($filepath, $jsonContent);

        $fileSize = Storage::size($filepath);
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);

        $this->newLine();
        $this->info("✅ Backup completed successfully!");
        $this->line("📁 File: storage/app/{$filepath}");
        $this->line("📊 Total tables: " . count($tables));
        $this->line("📈 Total records: {$totalRecords}");
        $this->line("💾 File size: {$fileSizeMB} MB");
        
        // Cleanup old backups (keep last 30 days)
        $this->cleanupOldBackups();

        return Command::SUCCESS;
    }

    /**
     * Cleanup backups older than 30 days
     */
    private function cleanupOldBackups()
    {
        $this->newLine();
        $this->info('🗑️  Cleaning up old backups...');
        
        $files = Storage::files('backups');
        $deleted = 0;
        
        foreach ($files as $file) {
            $lastModified = Storage::lastModified($file);
            $age = Carbon::now()->diffInDays(Carbon::createFromTimestamp($lastModified));
            
            // Delete files older than 30 days
            if ($age > 30) {
                Storage::delete($file);
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $this->line("   ✓ Deleted {$deleted} old backup(s)");
        } else {
            $this->line("   ✓ No old backups to delete");
        }
    }
}
