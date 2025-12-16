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
        Schema::create('laporan_tahunan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('tahun');
            $table->text('ringkasan_kegiatan')->nullable();
            $table->text('pencapaian')->nullable();
            $table->text('kendala')->nullable();
            $table->text('rencana_tahun_depan')->nullable();
            $table->enum('status', ['draft', 'selesai', 'ditandatangani'])->default('draft');
            $table->string('file_bukti_fisik')->nullable(); // File PDF yang sudah ditandatangani
            $table->timestamp('ditandatangani_pada')->nullable();
            $table->timestamps();

            // Unique: satu user hanya bisa punya satu laporan per tahun
            $table->unique(['user_id', 'tahun']);
            
            // Index untuk performa
            $table->index(['user_id', 'tahun']);
            $table->index('status');
        });

        // Tabel pivot untuk relasi Laporan Triwulanan dengan Laporan Tahunan
        Schema::create('laporan_tahunan_triwulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_tahunan_id')->constrained('laporan_tahunan')->onDelete('cascade');
            $table->foreignId('laporan_triwulanan_id')->constrained('laporan_triwulanan')->onDelete('cascade');
            $table->timestamps();

            $table->index('laporan_tahunan_id');
            $table->index('laporan_triwulanan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_tahunan_triwulanan');
        Schema::dropIfExists('laporan_tahunan');
    }
};
