<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class IncidentAttachment extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    /**
     * Konfigurasi Log Activity untuk Lampiran/Dokumen
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()                // Mencatat semua field yang ada di table (termasuk path & name)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('IncidentDetail')
            ->setDescriptionForEvent(fn(string $eventName) => "Incident Attachment has been {$eventName}");
    }

    /**
     * Mencegat aktivitas untuk memastikan informasi file tersimpan permanen di log properties
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties->toArray();

        // Jika event-nya 'deleted', kita ingin memastikan nama file terakhir tetap tercatat di log
        // agar tidak hanya muncul ID yang sudah tidak ada di DB.
        if ($eventName === 'deleted' && isset($properties['old'])) {
            $properties['attributes']['file_display'] = $properties['old']['file_name'] ?? 'Unknown File';
        }

        // Jika event 'created' atau 'updated'
        if (isset($properties['attributes']['file_name'])) {
            $properties['attributes']['file_display'] = $properties['attributes']['file_name'];
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
