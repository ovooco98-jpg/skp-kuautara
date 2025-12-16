<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RingkasanHarian extends Model
{
    use HasFactory;

    protected $table = 'ringkasan_harian';

    protected $fillable = [
        'user_id',
        'tanggal',
        'ringkasan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi dengan User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk ringkasan berdasarkan tanggal
     */
    public function scopeByTanggal($query, $tanggal)
    {
        return $query->whereDate('tanggal', $tanggal);
    }

    /**
     * Scope untuk ringkasan berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
