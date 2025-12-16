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
        Schema::create('lkh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->foreignId('kategori_kegiatan_id')->nullable()->constrained('kategori_kegiatan')->onDelete('set null');
            $table->string('uraian_kegiatan');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->text('hasil_output')->nullable();
            $table->text('kendala')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('lampiran')->nullable();
            $table->enum('status', ['draft', 'selesai'])->default('draft');
            $table->timestamps();

            // Index untuk performa query
            $table->index(['user_id', 'tanggal']);
            $table->index('status');
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lkh');
    }
};
