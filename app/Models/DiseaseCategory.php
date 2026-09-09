<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DiseaseCategory extends Model
{
    protected $fillable = ['name'];

    public function mcuResults(): BelongsToMany
    {
        return $this->belongsToMany(McuResult::class, 'disease_category_mcu_result');
    }
}
