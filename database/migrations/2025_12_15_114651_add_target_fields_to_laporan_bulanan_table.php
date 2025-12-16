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
        Schema::table('laporan_bulanan', function (Blueprint $table) {
            $table->string('target_lkh')->nullable()->after('rencana_bulan_depan');
            $table->string('target_durasi')->nullable()->after('target_lkh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_bulanan', function (Blueprint $table) {
            $table->dropColumn(['target_lkh', 'target_durasi']);
        });
    }
};
