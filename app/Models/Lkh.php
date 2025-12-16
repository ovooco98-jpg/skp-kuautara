<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lkh extends Model
{
    use HasFactory;

    protected $table = 'lkh';

    protected $fillable = [
        'user_id',
        'tanggal',
        'kategori_kegiatan_id',
        'uraian_kegiatan',
        'waktu_mulai',
        'waktu_selesai',
        'hasil_output',
        'kendala',
        'tindak_lanjut',
        'lampiran',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi dengan User (pegawai yang membuat LKH)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi dengan Kategori Kegiatan
     */
    public function kategoriKegiatan(): BelongsTo
    {
        return $this->belongsTo(KategoriKegiatan::class);
    }

    /**
     * Relasi many-to-many dengan Laporan Bulanan
     */
    public function laporanBulanan(): BelongsToMany
    {
        return $this->belongsToMany(LaporanBulanan::class, 'laporan_bulanan_lkh')
                    ->withTimestamps();
    }


    /**
     * Scope untuk LKH berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk LKH berdasarkan tanggal
     */
    public function scopeByTanggal($query, $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }

    /**
     * Scope untuk LKH berdasarkan bulan dan tahun
     */
    public function scopeByBulanTahun($query, $bulan, $tahun)
    {
        return $query->whereYear('tanggal', $tahun)
                     ->whereMonth('tanggal', $bulan);
    }

    /**
     * Scope untuk LKH berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get ringkasan harian untuk tanggal LKH ini
     */
    public function getRingkasanHarianAttribute()
    {
        return \App\Models\RingkasanHarian::byUser($this->user_id)
            ->byTanggal($this->tanggal)
            ->first();
    }

    /**
     * Hitung durasi kegiatan dalam jam
     */
    public function getDurasiAttribute(): float
    {
        try {
            $mulai = \Carbon\Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $this->waktu_mulai);
            $selesai = \Carbon\Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $this->waktu_selesai);
            
            // Jika waktu selesai lebih kecil dari waktu mulai, berarti melewati tengah malam
            if ($selesai->lt($mulai)) {
                $selesai->addDay();
            }
            
            return round($mulai->diffInMinutes($selesai) / 60, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check apakah LKH bisa di-edit (selalu bisa karena tidak ada approval)
     */
    public function canEdit(): bool
    {
        return true; // Selalu bisa di-edit
    }
}
