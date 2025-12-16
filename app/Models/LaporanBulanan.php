<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LaporanBulanan extends Model
{
    use HasFactory;

    protected $table = 'laporan_bulanan';

    protected $fillable = [
        'user_id',
        'bulan',
        'tahun',
        'ringkasan_kegiatan',
        'pencapaian',
        'kendala',
        'rencana_bulan_depan',
        'target_lkh',
        'target_durasi',
        'status',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
    ];

    /**
     * Relasi dengan User (pegawai)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi many-to-many dengan LKH
     */
    public function lkh(): BelongsToMany
    {
        return $this->belongsToMany(Lkh::class, 'laporan_bulanan_lkh')
                    ->withTimestamps();
    }

    /**
     * Relasi many-to-many dengan SKP
     */
    public function skp(): BelongsToMany
    {
        return $this->belongsToMany(Skp::class, 'skp_laporan_bulanan')
                    ->withTimestamps();
    }

    /**
     * Scope untuk laporan berdasarkan bulan dan tahun
     */
    public function scopeByBulanTahun($query, $bulan, $tahun)
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }

    /**
     * Scope untuk laporan berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get nama bulan
     */
    public function getNamaBulanAttribute(): string
    {
        $bulan = \Carbon\Carbon::create($this->tahun, $this->bulan, 1);
        return $bulan->locale('id')->translatedFormat('F Y');
    }

    /**
     * Get total LKH dalam laporan ini
     */
    public function getTotalLkhAttribute(): int
    {
        return $this->lkh()->count();
    }

    /**
     * Get total durasi dari semua LKH
     */
    public function getTotalDurasiAttribute(): float
    {
        return $this->lkh()->get()->sum('durasi');
    }
}
