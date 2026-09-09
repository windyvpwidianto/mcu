<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal.
     * Sesuaikan dengan nama kolom di migrasi terbaru kita.
     */
    protected $fillable = [
        'key',
        'en',
        'id_text',
        'group'
    ];

    /**
     * Jika kamu ingin memastikan 'key' selalu tersimpan dalam format tertentu,
     * kamu bisa menambahkan casting atau mutator di sini jika perlu.
     */
}
