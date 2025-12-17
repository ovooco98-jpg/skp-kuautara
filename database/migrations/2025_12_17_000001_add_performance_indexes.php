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
        Schema::table('lkh', function (Blueprint $table) {
            // Index untuk query yang sering digunakan
            if (!$this->indexExists('lkh', 'lkh_user_id_tanggal_status_index')) {
                $table->index(['user_id', 'tanggal', 'status'], 'lkh_user_id_tanggal_status_index');
            }
            if (!$this->indexExists('lkh', 'lkh_tanggal_status_index')) {
                $table->index(['tanggal', 'status'], 'lkh_tanggal_status_index');
            }
            if (!$this->indexExists('lkh', 'lkh_created_at_index')) {
                $table->index('created_at', 'lkh_created_at_index');
            }
        });

        Schema::table('laporan_bulanan', function (Blueprint $table) {
            // Index untuk query laporan bulanan
            if (!$this->indexExists('laporan_bulanan', 'laporan_bulanan_user_tahun_bulan_index')) {
                $table->index(['user_id', 'tahun', 'bulan'], 'laporan_bulanan_user_tahun_bulan_index');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Index untuk query user aktif
            if (!$this->indexExists('users', 'users_role_is_active_index')) {
                $table->index(['role', 'is_active'], 'users_role_is_active_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lkh', function (Blueprint $table) {
            $table->dropIndex('lkh_user_id_tanggal_status_index');
            $table->dropIndex('lkh_tanggal_status_index');
            $table->dropIndex('lkh_created_at_index');
        });

        Schema::table('laporan_bulanan', function (Blueprint $table) {
            $table->dropIndex('laporan_bulanan_user_tahun_bulan_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_is_active_index');
        });
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        $result = $connection->select(
            "SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$databaseName, $table, $index]
        );
        
        return $result[0]->count > 0;
    }
};

