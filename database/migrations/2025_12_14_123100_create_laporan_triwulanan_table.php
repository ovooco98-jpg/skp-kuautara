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
        Schema::create('laporan_triwulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('triwulan'); // 1-4
            $table->integer('tahun');
            $table->text('ringkasan_kegiatan')->nullable();
            $table->text('pencapaian')->nullable();
            $table->text('kendala')->nullable();
            $table->text('rencana_triwulan_depan')->nullable();
            $table->enum('status', ['draft', 'selesai', 'ditandatangani'])->default('draft');
            $table->string('file_bukti_fisik')->nullable(); // File PDF yang sudah ditandatangani
            $table->timestamp('ditandatangani_pada')->nullable();
            $table->timestamps();

            // Unique: satu user hanya bisa punya satu laporan per triwulan-tahun
            $table->unique(['user_id', 'triwulan', 'tahun']);
            
            // Index untuk performa
            $table->index(['user_id', 'tahun', 'triwulan']);
            $table->index('status');
        });

        // Tabel pivot untuk relasi Laporan Bulanan dengan Laporan Triwulanan
        Schema::create('laporan_triwulanan_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_triwulanan_id')->constrained('laporan_triwulanan')->onDelete('cascade');
            $table->foreignId('laporan_bulanan_id')->constrained('laporan_bulanan')->onDelete('cascade');
            $table->timestamps();

            $table->index('laporan_triwulanan_id');
            $table->index('laporan_bulanan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_triwulanan_bulanan');
        Schema::dropIfExists('laporan_triwulanan');
    }
};
