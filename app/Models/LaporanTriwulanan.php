<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LaporanTriwulanan extends Model
{
    use HasFactory;

    protected $table = 'laporan_triwulanan';

    protected $fillable = [
        'user_id',
        'triwulan',
        'tahun',
        'ringkasan_kegiatan',
        'pencapaian',
        'kendala',
        'rencana_triwulan_depan',
        'status',
        'file_bukti_fisik',
        'ditandatangani_pada',
    ];

    protected $casts = [
        'triwulan' => 'integer',
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
     * Relasi many-to-many dengan Laporan Bulanan
     */
    public function laporanBulanan(): BelongsToMany
    {
        return $this->belongsToMany(LaporanBulanan::class, 'laporan_triwulanan_bulanan')
                    ->withTimestamps();
    }

    /**
     * Relasi many-to-many dengan SKP
     */
    public function skp(): BelongsToMany
    {
        return $this->belongsToMany(Skp::class, 'skp_laporan_triwulanan')
                    ->withTimestamps();
    }

    /**
     * Scope untuk laporan berdasarkan triwulan dan tahun
     */
    public function scopeByTriwulanTahun($query, $triwulan, $tahun)
    {
        return $query->where('triwulan', $triwulan)->where('tahun', $tahun);
    }

    /**
     * Scope untuk laporan berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get nama triwulan
     */
    public function getNamaTriwulanAttribute(): string
    {
        $bulanMulai = ($this->triwulan - 1) * 3 + 1;
        $bulanSelesai = $this->triwulan * 3;
        
        $namaBulanMulai = \Carbon\Carbon::create($this->tahun, $bulanMulai, 1)->locale('id')->translatedFormat('F');
        $namaBulanSelesai = \Carbon\Carbon::create($this->tahun, $bulanSelesai, 1)->locale('id')->translatedFormat('F');
        
        return "Triwulan {$this->triwulan} ({$namaBulanMulai} - {$namaBulanSelesai} {$this->tahun})";
    }

    /**
     * Get total LKH dari semua laporan bulanan
     */
    public function getTotalLkhAttribute(): int
    {
        return $this->laporanBulanan->sum('total_lkh');
    }

    /**
     * Get total durasi dari semua laporan bulanan
     */
    public function getTotalDurasiAttribute(): float
    {
        return $this->laporanBulanan->sum('total_durasi');
    }
}
