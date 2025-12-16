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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->nullable()->unique()->after('name');
            $table->enum('role', ['kepala_kua', 'penghulu', 'penyuluh_agama', 'pelaksana'])->default('pelaksana')->after('email');
            $table->string('jabatan')->nullable()->after('role');
            $table->string('unit_kerja')->default('KUA Kecamatan Banjarmasin Utara')->after('jabatan');
            $table->boolean('is_active')->default(true)->after('unit_kerja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'role', 'jabatan', 'unit_kerja', 'is_active']);
        });
    }
};
