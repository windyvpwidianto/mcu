<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class BodyPart extends Model
{

    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal.
     * Sesuaikan dengan migration body_parts Anda.
     */
    protected $fillable = [
        'name',
        'name_en',
        'category',
        'code',
    ];

    public function getDisplayNameAttribute()
    {
        $columnEn = 'name_en';
        return (app()->getLocale() === 'en' && !empty($this->$columnEn))
            ? $this->$columnEn
            : $this->name;
    }
    public function incidents(): BelongsToMany
    {
        return $this->belongsToMany(Incident::class, 'incident_body_parts', 'body_part_id', 'incident_id');
    }
    // Scope untuk mencari berdasarkan Nama (ID atau EN)
    public function scopeSearchName(Builder $query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('name_en', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%");
        });
    }

    // Scope untuk mencari berdasarkan Kategori
    public function scopeSearchCategory(Builder $query, $category)
    {
        return $query->when($category, function ($q) use ($category) {
            $q->where('category', $category);
        });
    }
}
