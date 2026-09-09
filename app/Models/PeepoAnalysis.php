<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class PeepoAnalysis extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    /**
     * Konfigurasi Log Activity untuk Analisis PEEPO
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('IncidentDetail')
            ->setDescriptionForEvent(fn(string $eventName) => "PEEPO Analysis has been {$eventName}");
    }

    /**
     * Mencegat aktivitas untuk memberikan konteks elemen PEEPO
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties->toArray();

        // Menyimpan kategori (People/Equipment/etc) sebagai context di attributes
        // Ini berguna agar di Blade kita bisa langsung tahu elemen PEEPO mana yang diubah
        if (isset($properties['attributes']['category'])) {
            $properties['attributes']['peepo_category'] = strtoupper($properties['attributes']['category']);
        } elseif (isset($properties['old']['category'])) {
            $properties['attributes']['peepo_category'] = strtoupper($properties['old']['category']);
        }

        $activity->properties = collect($properties);
    }

    /**
     * Relasi balik ke laporan utama
     */
    public function incidentReport(): BelongsTo
    {
        return $this->belongsTo(IncidentReport::class);
    }
}
