<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('bulan'); // 1-12
            $table->integer('tahun');
            $table->text('ringkasan_kegiatan')->nullable(); // Kesimpulan/ringkasan kegiatan bulanan
            $table->text('pencapaian')->nullable(); // Pencapaian bulan ini
            $table->text('kendala')->nullable(); // Kendala yang dihadapi
            $table->text('rencana_bulan_depan')->nullable(); // Rencana bulan depan
            $table->enum('status', ['draft', 'selesai'])->default('draft');
            $table->timestamps();

            // Unique: satu user hanya bisa punya satu laporan per bulan-tahun
            $table->unique(['user_id', 'bulan', 'tahun']);
            
            // Index untuk performa
            $table->index(['user_id', 'tahun', 'bulan']);
            $table->index('status');
        });

        // Tabel pivot untuk relasi LKH dengan Laporan Bulanan
        Schema::create('laporan_bulanan_lkh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_bulanan_id')->constrained('laporan_bulanan')->onDelete('cascade');
            $table->foreignId('lkh_id')->constrained('lkh')->onDelete('cascade');
            $table->timestamps();

            // Unique: satu LKH hanya bisa di-link ke satu laporan bulanan
            $table->unique('lkh_id');
            $table->index('laporan_bulanan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_bulanan_lkh');
        Schema::dropIfExists('laporan_bulanan');
    }
};
