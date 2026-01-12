<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RestoreDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore 
                            {file : Backup file to restore from}
                            {--tables=* : Specific tables to restore}
                            {--force : Force restore without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from backup file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        
        if (!Storage::exists($file)) {
            $this->error("❌ Backup file not found: {$file}");
            $this->line("\n📂 Available backups:");
            
            // List available backups
            $backups = Storage::files('backups');
            foreach ($backups as $backup) {
                $size = round(Storage::size($backup) / 1024 / 1024, 2);
                $date = Carbon::createFromTimestamp(Storage::lastModified($backup))->format('Y-m-d H:i:s');
                $this->line("   • {$backup} ({$size} MB) - {$date}");
            }
            
            return Command::FAILURE;
        }

        $this->warn("⚠️  WARNING: This will overwrite existing data!");
        
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to continue?', false)) {
                $this->info('Restore cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('🔄 Starting database restore...');

        // Read backup file
        $jsonContent = Storage::get($file);
        
        // Check if compressed
        if (str_ends_with($file, '.gz')) {
            $jsonContent = gzuncompress($jsonContent);
        }
        
        $backupData = json_decode($jsonContent, true);
        
        if (!$backupData) {
            $this->error('❌ Failed to parse backup file');
            return Command::FAILURE;
        }

        // Show backup info
        if (isset($backupData['_metadata'])) {
            $this->info("\n📊 Backup Information:");
            $this->line("   Date: " . $backupData['_metadata']['backup_date']);
            $this->line("   Tables: " . $backupData['_metadata']['total_tables']);
            $this->line("   Records: " . $backupData['_metadata']['total_records']);
        }

        $tablesToRestore = $this->option('tables') ?: array_keys($backupData);
        $tablesToRestore = array_diff($tablesToRestore, ['_metadata']);

        $totalRestored = 0;

        DB::beginTransaction();

        try {
            foreach ($tablesToRestore as $table) {
                if (!isset($backupData[$table])) {
                    $this->warn("⚠️  Table {$table} not found in backup");
                    continue;
                }

                $this->info("\n📥 Restoring table: {$table}");
                
                $data = $backupData[$table]['data'];
                $count = count($data);
                
                // Clear existing data
                DB::table($table)->truncate();
                $this->line("   ✓ Cleared existing data");
                
                // Insert in chunks
                $chunks = array_chunk($data, 100);
                $bar = $this->output->createProgressBar(count($chunks));
                
                foreach ($chunks as $chunk) {
                    // Convert stdClass to array
                    $chunk = array_map(function($item) {
                        return (array) $item;
                    }, $chunk);
                    
                    DB::table($table)->insert($chunk);
                    $bar->advance();
                }
                
                $bar->finish();
                $this->newLine();
                $this->line("   ✓ Restored {$count} records");
                
                $totalRestored += $count;
            }

            DB::commit();
            
            $this->newLine();
            $this->info("✅ Restore completed successfully!");
            $this->line("📈 Total records restored: {$totalRestored}");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Restore failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
