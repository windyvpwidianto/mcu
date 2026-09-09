<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceMaster extends Model
{
    protected $fillable = [
        'name',
        'description', // Tambahkan ini
        'class',       // Tambahkan ini
        'duration_months',
        'title',       // Tambahkan ini
        'status',
    ];

    // Casts untuk memastikan tipe data konsisten
    protected $casts = [
        'duration_months' => 'integer',
        'status' => 'boolean',
    ];
    /**
     * Helper untuk mengecek apakah master ini permanen
     */
    public function isLifetime(): bool
    {
        return is_null($this->duration_months);
    }
    /**
     * Relasi ke tabel transaksi Compliance
     */
    public function compliances(): HasMany
    {
        return $this->hasMany(Compliance::class);
    }
     public function scopeSearchClass($query, $term)
    {
        return $query->where('class',  'like', '%' . $term . '%');
    }
    public function scopeSearchName($query, $term)
    {
        return $query->where('name',  'like', '%' . $term . '%');
    }
}
