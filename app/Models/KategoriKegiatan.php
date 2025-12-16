<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriKegiatan extends Model
{
    use HasFactory;

    protected $table = 'kategori_kegiatan';

    protected $fillable = [
        'nama',
        'deskripsi',
        'role',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi dengan LKH
     */
    public function lkh()
    {
        return $this->hasMany(Lkh::class);
    }

    /**
     * Scope untuk kategori aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk kategori berdasarkan role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->where('role', $role)
              ->orWhere('role', 'semua');
        });
    }
}
