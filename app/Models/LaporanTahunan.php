<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LaporanTahunan extends Model
{
    use HasFactory;

    protected $table = 'laporan_tahunan';

    protected $fillable = [
        'user_id',
        'tahun',
        'ringkasan_kegiatan',
        'pencapaian',
        'kendala',
        'rencana_tahun_depan',
        'status',
        'file_bukti_fisik',
        'ditandatangani_pada',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'ditandatangani_pada' => 'datetime',
    ];

    /**
     * Relasi dengan User (pegawai)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi many-to-many dengan Laporan Triwulanan
     */
    public function laporanTriwulanan(): BelongsToMany
    {
        return $this->belongsToMany(LaporanTriwulanan::class, 'laporan_tahunan_triwulanan')
                    ->withTimestamps();
    }

    /**
     * Relasi many-to-many dengan SKP
     */
    public function skp(): BelongsToMany
    {
        return $this->belongsToMany(Skp::class, 'skp_laporan_tahunan')
                    ->withTimestamps();
    }

    /**
     * Scope untuk laporan berdasarkan tahun
     */
    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Scope untuk laporan berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get nama tahun
     */
    public function getNamaTahunAttribute(): string
    {
        return "Tahun {$this->tahun}";
    }

    /**
     * Get total LKH dari semua laporan triwulanan
     */
    public function getTotalLkhAttribute(): int
    {
        return $this->laporanTriwulanan->sum('total_lkh');
    }

    /**
     * Get total durasi dari semua laporan triwulanan
     */
    public function getTotalDurasiAttribute(): float
    {
        return $this->laporanTriwulanan->sum('total_durasi');
    }
}
