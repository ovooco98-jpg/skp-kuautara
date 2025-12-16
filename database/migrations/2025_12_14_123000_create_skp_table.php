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
        Schema::create('skp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('tahun'); // Tahun penilaian SKP
            $table->string('kegiatan_tugas_jabatan'); // Nama kegiatan tugas jabatan
            $table->text('rincian_tugas')->nullable(); // Rincian tugas, tanggung jawab, wewenang
            $table->text('target_kuantitas')->nullable(); // Target kuantitas
            $table->text('target_kualitas')->nullable(); // Target kualitas
            $table->text('target_waktu')->nullable(); // Target waktu
            $table->text('target_biaya')->nullable(); // Target biaya (jika ada)
            $table->text('realisasi_kuantitas')->nullable(); // Realisasi kuantitas (dari laporan bulanan)
            $table->text('realisasi_kualitas')->nullable(); // Realisasi kualitas
            $table->text('realisasi_waktu')->nullable(); // Realisasi waktu
            $table->text('realisasi_biaya')->nullable(); // Realisasi biaya
            $table->decimal('nilai_capaian', 5, 2)->nullable(); // Nilai capaian SKP (0-100)
            $table->text('catatan')->nullable(); // Catatan dari atasan
            $table->enum('status', ['draft', 'disetujui', 'dinilai', 'selesai'])->default('draft');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null'); // Atasan yang menyetujui
            $table->timestamp('disetujui_pada')->nullable();
            $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->onDelete('set null'); // Atasan yang menilai
            $table->timestamp('dinilai_pada')->nullable();
            
            // Relasi cascading: SKP staff mengacu pada SKP atasan
            $table->foreignId('skp_atasan_id')->nullable()->constrained('skp')->onDelete('set null');
            
            $table->timestamps();

            // Index untuk performa
            $table->index(['user_id', 'tahun']);
            $table->index('status');
            $table->index('tahun');
        });

        // Tabel pivot untuk relasi SKP dengan Laporan Bulanan
        Schema::create('skp_laporan_bulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skp_id')->constrained('skp')->onDelete('cascade');
            $table->foreignId('laporan_bulanan_id')->constrained('laporan_bulanan')->onDelete('cascade');
            $table->timestamps();

            // Unique: satu laporan bulanan bisa di-link ke beberapa SKP (untuk agregasi)
            $table->index('skp_id');
            $table->index('laporan_bulanan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skp_laporan_bulanan');
        Schema::dropIfExists('skp');
    }
};
