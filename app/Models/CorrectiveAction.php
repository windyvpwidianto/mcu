<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class CorrectiveAction extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    protected $casts = [
        'due_date' => 'date',
        'actual_completion_date' => 'date',
    ];

    /**
     * Konfigurasi Logging
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('IncidentDetail')
            ->setDescriptionForEvent(fn(string $eventName) => "Corrective Action has been {$eventName}");
    }

    /**
     * Mencegat aktivitas untuk merekam label PIC dan format tanggal
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties->toArray();

        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $field => $value) {
                switch ($field) {
                    case 'pic_user_id':
                        // Rekam nama PIC untuk nilai baru
                        $properties['attributes']['pic_user_id_label'] = \App\Models\User::find($value)?->name ?? 'Unknown PIC';

                        // Rekam nama PIC untuk nilai lama
                        if (isset($properties['old']['pic_user_id'])) {
                            $oldId = $properties['old']['pic_user_id'];
                            $properties['old']['pic_user_id_label'] = \App\Models\User::find($oldId)?->name ?? 'Unknown PIC';
                        }
                        break;

                    case 'status':
                        // Memastikan status tersimpan dengan format huruf besar untuk estetika log
                        $properties['attributes']['status_label'] = strtoupper($value);
                        if (isset($properties['old']['status'])) {
                            $properties['old']['status_label'] = strtoupper($properties['old']['status']);
                        }
                        break;
                }
            }

            // Tambahkan konteks deskripsi tindakan agar log mudah diidentifikasi
            if (isset($properties['attributes']['action_description'])) {
                $properties['attributes']['action_summary'] = $properties['attributes']['action_description'];
            } elseif (isset($properties['old']['action_description'])) {
                $properties['attributes']['action_summary'] = $properties['old']['action_description'];
            }

            $activity->properties = collect($properties);
        }
    }

    /**
     * Relasi Induk
     */
    public function incidentReport(): BelongsTo
    {
        return $this->belongsTo(IncidentReport::class);
    }

    /**
     * Relasi ke User (PIC)
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }
}
