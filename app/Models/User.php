<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'role',
        'jabatan',
        'pangkat_gol',
        'unit_kerja',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi dengan LKH yang dibuat user
     */
    public function lkh()
    {
        return $this->hasMany(Lkh::class);
    }

    /**
     * Relasi dengan LKH yang di-approve user (jika Kepala KUA)
     */
    public function approvedLkh()
    {
        return $this->hasMany(Lkh::class, 'approved_by');
    }

    /**
     * Relasi dengan Laporan Bulanan yang dibuat user
     */
    public function laporanBulanan()
    {
        return $this->hasMany(LaporanBulanan::class);
    }

    /**
     * Relasi dengan SKP yang dibuat user
     */
    public function skp()
    {
        return $this->hasMany(Skp::class);
    }

    /**
     * Relasi dengan SKP yang disetujui user (jika atasan)
     */
    public function skpDisetujui()
    {
        return $this->hasMany(Skp::class, 'disetujui_oleh');
    }

    /**
     * Relasi dengan SKP yang dinilai user (jika atasan)
     */
    public function skpDinilai()
    {
        return $this->hasMany(Skp::class, 'dinilai_oleh');
    }

    /**
     * Relasi dengan Laporan Triwulanan yang dibuat user
     */
    public function laporanTriwulanan()
    {
        return $this->hasMany(LaporanTriwulanan::class);
    }

    /**
     * Relasi dengan Laporan Tahunan yang dibuat user
     */
    public function laporanTahunan()
    {
        return $this->hasMany(LaporanTahunan::class);
    }

    /**
     * Check apakah user adalah Kepala KUA
     */
    public function isKepalaKua(): bool
    {
        return $this->role === 'kepala_kua';
    }

    /**
     * Check apakah user adalah Penghulu
     */
    public function isPenghulu(): bool
    {
        return $this->role === 'penghulu';
    }

    /**
     * Check apakah user adalah Penyuluh Agama
     */
    public function isPenyuluhAgama(): bool
    {
        return $this->role === 'penyuluh_agama';
    }

    /**
     * Check apakah user adalah Pelaksana
     */
    public function isPelaksana(): bool
    {
        return $this->role === 'pelaksana';
    }

    /**
     * Scope untuk user aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk user berdasarkan role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
