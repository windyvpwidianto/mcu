<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class InvolvedPerson extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];
    protected $table = 'involved_persons';

    /**
     * Konfigurasi Log Activity
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('IncidentDetail')
            ->setDescriptionForEvent(fn(string $eventName) => "Involved Person has been {$eventName}");
    }

    /**
     * Mencegat aktivitas untuk merekam identitas personil yang terlibat
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties->toArray();

        // Rekam nama personil sebagai label utama di dalam log properties
        // Ini memastikan jika record dihapus, kita tetap tahu siapa yang dihapus
        if (isset($properties['attributes']['nama'])) {
            $properties['attributes']['person_name'] = $properties['attributes']['nama'];
        } elseif (isset($properties['old']['nama'])) {
            $properties['attributes']['person_name'] = $properties['old']['nama'];
        }

        // Jika ada NIK, sertakan juga untuk akurasi data mining
        if (isset($properties['attributes']['nik'])) {
            $properties['attributes']['person_nik'] = $properties['attributes']['nik'];
        }

        $activity->properties = collect($properties);
    }

    /**
     * Relasi ke laporan induk
     */
    public function incidentReport(): BelongsTo
    {
        return $this->belongsTo(IncidentReport::class);
    }
}
