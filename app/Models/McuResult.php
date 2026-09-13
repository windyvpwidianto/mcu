<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class McuResult extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'follow_up_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->useLogName('mcu')
            ->setDescriptionForEvent(fn(string $eventName) => "MCU Result has been {$eventName}")
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(McuRecord::class, 'mcu_record_id');
    }
    // Tambahkan di dalam class McuResult
    public function diseaseCategories(): BelongsToMany
    {
        return $this->belongsToMany(DiseaseCategory::class, 'disease_category_mcu_result');
    }
}
