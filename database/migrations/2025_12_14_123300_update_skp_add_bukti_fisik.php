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
        Schema::table('skp', function (Blueprint $table) {
            // Tambah field untuk periode (triwulan atau tahunan)
            $table->integer('triwulan')->nullable()->after('tahun'); // 1-4, null jika tahunan
            $table->enum('periode', ['triwulan', 'tahunan'])->default('tahunan')->after('triwulan');
            
            // Tambah field untuk upload bukti fisik (file PDF yang sudah ditandatangani)
            $table->string('file_bukti_fisik')->nullable()->after('nilai_capaian');
            $table->string('link_skp_eksternal')->nullable()->after('file_bukti_fisik'); // Link ke sistem SKP eksternal (e-Kinerja)
            $table->timestamp('uploaded_at')->nullable()->after('link_skp_eksternal'); // Waktu upload ke sistem eksternal
        });

        // Tabel pivot untuk relasi SKP dengan Laporan Triwulanan
        Schema::create('skp_laporan_triwulanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skp_id')->constrained('skp')->onDelete('cascade');
            $table->foreignId('laporan_triwulanan_id')->constrained('laporan_triwulanan')->onDelete('cascade');
            $table->timestamps();

            $table->index('skp_id');
            $table->index('laporan_triwulanan_id');
        });

        // Tabel pivot untuk relasi SKP dengan Laporan Tahunan
        Schema::create('skp_laporan_tahunan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skp_id')->constrained('skp')->onDelete('cascade');
            $table->foreignId('laporan_tahunan_id')->constrained('laporan_tahunan')->onDelete('cascade');
            $table->timestamps();

            $table->index('skp_id');
            $table->index('laporan_tahunan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skp_laporan_tahunan');
        Schema::dropIfExists('skp_laporan_triwulanan');
        
        Schema::table('skp', function (Blueprint $table) {
            $table->dropColumn(['triwulan', 'periode', 'file_bukti_fisik', 'link_skp_eksternal', 'uploaded_at']);
        });
    }
};
