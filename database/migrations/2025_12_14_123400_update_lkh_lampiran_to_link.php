<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: Field lampiran tetap string, tapi sekarang digunakan untuk menyimpan link drive
     * bukan file path. Tidak perlu ubah struktur, cukup ubah cara handle di controller.
     */
    public function up(): void
    {
        // Tidak perlu ubah struktur, field lampiran sudah string
        // Cukup ubah cara handle di controller dan views
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback
    }
};
