<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class McuResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'follow_up_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(McuParticipant::class, 'mcu_participant_id');
    }
    // Tambahkan di dalam class McuResult
    public function diseaseCategories(): BelongsToMany
    {
        return $this->belongsToMany(DiseaseCategory::class, 'disease_category_mcu_result');
    }
}
