<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skp extends Model
{
    use HasFactory;

    protected $table = 'skp';

    protected $fillable = [
        'user_id',
        'tahun',
        'triwulan',
        'periode',
        'kegiatan_tugas_jabatan',
        'rincian_tugas',
        'target_kuantitas',
        'target_kualitas',
        'target_waktu',
        'target_biaya',
        'realisasi_kuantitas',
        'realisasi_kualitas',
        'realisasi_waktu',
        'realisasi_biaya',
        'nilai_capaian',
        'catatan',
        'status',
        'disetujui_oleh',
        'disetujui_pada',
        'dinilai_oleh',
        'dinilai_pada',
        'skp_atasan_id',
        'file_bukti_fisik',
        'link_skp_eksternal',
        'uploaded_at',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'triwulan' => 'integer',
        'nilai_capaian' => 'decimal:2',
        'disetujui_pada' => 'datetime',
        'dinilai_pada' => 'datetime',
        'uploaded_at' => 'datetime',
    ];

    /**
     * Relasi dengan User (pegawai yang memiliki SKP)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi dengan User yang menyetujui SKP
     */
    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Relasi dengan User yang menilai SKP
     */
    public function dinilaiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }

    /**
     * Relasi cascading: SKP atasan (untuk prinsip cascading)
     */
    public function skpAtasan(): BelongsTo
    {
        return $this->belongsTo(Skp::class, 'skp_atasan_id');
    }

    /**
     * Relasi: SKP staff yang mengacu pada SKP ini (jika Kepala KUA)
     */
    public function skpStaff(): HasMany
    {
        return $this->hasMany(Skp::class, 'skp_atasan_id');
    }

    /**
     * Relasi many-to-many dengan Laporan Bulanan
     */
    public function laporanBulanan(): BelongsToMany
    {
        return $this->belongsToMany(LaporanBulanan::class, 'skp_laporan_bulanan')
                    ->withTimestamps();
    }

    /**
     * Relasi many-to-many dengan Laporan Triwulanan
     */
    public function laporanTriwulanan(): BelongsToMany
    {
        return $this->belongsToMany(LaporanTriwulanan::class, 'skp_laporan_triwulanan')
                    ->withTimestamps();
    }

    /**
     * Relasi many-to-many dengan Laporan Tahunan
     */
    public function laporanTahunan(): BelongsToMany
    {
        return $this->belongsToMany(LaporanTahunan::class, 'skp_laporan_tahunan')
                    ->withTimestamps();
    }

    /**
     * Scope untuk SKP berdasarkan tahun
     */
    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Scope untuk SKP berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk SKP berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk SKP yang mengacu pada SKP atasan tertentu
     */
    public function scopeBySkpAtasan($query, $skpAtasanId)
    {
        return $query->where('skp_atasan_id', $skpAtasanId);
    }

    /**
     * Hitung realisasi dari laporan bulanan yang terhubung
     */
    public function hitungRealisasiDariLaporanBulanan(): void
    {
        $laporanBulanan = $this->laporanBulanan;
        
        if ($laporanBulanan->isEmpty()) {
            return;
        }

        // Agregasi data dari laporan bulanan
        $totalLkh = $laporanBulanan->sum(function($laporan) {
            return $laporan->total_lkh;
        });

        $totalDurasi = $laporanBulanan->sum(function($laporan) {
            return $laporan->total_durasi;
        });

        // Update realisasi (contoh sederhana, bisa disesuaikan dengan kebutuhan)
        $this->realisasi_kuantitas = "Total {$totalLkh} kegiatan dari {$laporanBulanan->count()} laporan bulanan";
        $this->realisasi_waktu = "Total {$totalDurasi} jam";
        
        // Hitung nilai capaian (contoh sederhana)
        // Bisa disesuaikan dengan formula yang lebih kompleks
        if ($this->target_kuantitas) {
            // Extract angka dari target (contoh: "100 kegiatan" -> 100)
            preg_match('/(\d+)/', $this->target_kuantitas, $matches);
            $targetAngka = isset($matches[1]) ? (int)$matches[1] : 0;
            
            if ($targetAngka > 0) {
                $persentase = min(100, ($totalLkh / $targetAngka) * 100);
                $this->nilai_capaian = round($persentase, 2);
            }
        }

        $this->save();
    }

    /**
     * Check apakah SKP bisa di-edit
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'disetujui']);
    }

    /**
     * Check apakah SKP bisa dinilai
     */
    public function canDinilai(): bool
    {
        return $this->status === 'disetujui';
    }

    /**
     * Get label status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'disetujui' => 'Disetujui',
            'dinilai' => 'Dinilai',
            'selesai' => 'Selesai',
            default => 'Unknown'
        };
    }
}
