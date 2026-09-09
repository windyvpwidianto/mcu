<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class InvestigationTeam extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    /**
     * Konfigurasi Log Activity untuk Tim Investigasi
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log semua field agar incident_report_id selalu terbawa
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('IncidentDetail') // Pastikan log name ini konsisten
            ->setDescriptionForEvent(fn(string $eventName) => "Investigation Team member has been {$eventName}");
    }

    /**
     * Mencegat aktivitas untuk merekam nama User di tim investigasi
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties->toArray();

        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $field => $value) {
                if ($field === 'user_id') {
                    // Cari nama user untuk nilai baru
                    $properties['attributes']['user_id_label'] = \App\Models\User::find($value)?->name ?? 'Unknown User';

                    // Cari nama user untuk nilai lama (jika ada/update)
                    if (isset($properties['old']['user_id'])) {
                        $oldId = $properties['old']['user_id'];
                        $properties['old']['user_id_label'] = \App\Models\User::find($oldId)?->name ?? 'Unknown User';
                    }
                }
            }
            $activity->properties = collect($properties);
        }
    }

    /**
     * Relasi balik ke laporan utama
     */
    public function incidentReport(): BelongsTo
    {
        return $this->belongsTo(IncidentReport::class);
    }

    /**
     * Relasi ke tabel User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
