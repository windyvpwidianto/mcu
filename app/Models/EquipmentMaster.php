<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentMaster extends Model
{
   protected $fillable = ['type', 'location_id', 'specific_location', 'technical_data', 'is_active'];

    protected $casts = [
        'technical_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
        public function scopeByArea($query, $name)
    {
        return $query->whereHas('location', function ($q) use ($name) {
            $q->where('name', 'like', "%{$name}%");
        });
    }
    public function scopeSearch($query, $type)
    {
        return $query->where('type', 'like', "%{$type}%");
    }
    public function scopeSpesificLocation($query, $specific_location)
    {
        return $query->where('specific_location', 'like', "%{$specific_location}%");
    }


}
