<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HazardChat extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi secara massal.
     * Sesuaikan dengan nama kolom di migration Anda.
     */
    protected $fillable = [
        'hazard_report_id',
        'user_id',
        'message',
        'read_at',
    ];
    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Relasi Balik ke HazardReport.
     * Mengetahui pesan ini milik laporan hazard yang mana.
     */
    public function hazardReport(): BelongsTo
    {
        // Parameter kedua adalah foreign key di tabel hazard_chats
        return $this->belongsTo(Hazard::class, 'hazard_report_id');
    }

    /**
     * Relasi ke User (Pengirim Pesan).
     * Digunakan untuk menampilkan nama dan foto pengirim di chat bubble.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
