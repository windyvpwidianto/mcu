<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class TimelineAnalysis extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = [
        'analysis_steps' => 'array',
    ];

    /**
     * Konfigurasi Log Activity untuk Timeline & 5 Whys
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('IncidentDetail')
            ->setDescriptionForEvent(fn(string $eventName) => "Timeline Analysis has been {$eventName}");
    }

    /**
     * Mencegat aktivitas untuk memberikan konteks pada log timeline
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties->toArray();

        // Menyimpan ringkasan event agar log mudah dibaca di tabel utama
        if (isset($properties['attributes']['event_description'])) {
            $properties['attributes']['timeline_summary'] = $properties['attributes']['event_description'];
        } elseif (isset($properties['old']['event_description'])) {
            $properties['attributes']['timeline_summary'] = $properties['old']['event_description'];
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
