<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\LogOptions;
class WpiFinding extends Model
{
    use LogsActivity;
    protected $fillable = [
    'wpi_report_id',
    'ohs_risk',
    'description',
    'prevention_action',
    'pic_responsible',
    'due_date',
    'completion_date',
    'photos',
    'photos_prevention'
];
    // App/Models/WpiFinding.php
    protected $casts = [
        'photos' => 'array',
        'photos_prevention' => 'array',
        'due_date' => 'date',
        'completion_date' => 'date',
        'pic_responsible' => 'array', // Otomatis mengubah Array ke JSON saat simpan, dan sebaliknya
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            // Kita gunakan log_name yang sama dengan report agar mudah ditarik di view audit trail
            ->useLogName('WpiReport')
            ->setDescriptionForEvent(fn(string $eventName) => "Temuan (Finding) telah di {$eventName}");
    }

    /**
     * Mempercantik tampilan PIC di Audit Trail
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties->toArray();

        foreach (['attributes', 'old'] as $key) {
            if (!isset($properties[$key])) continue;

            // Jika PIC tersimpan sebagai string (hasil implode '|'), kita rapikan tampilannya
            if (isset($properties[$key]['pic_responsible'])) {
                $pic = $properties[$key]['pic_responsible'];
                $properties[$key]['pic_responsible_label'] = str_replace('|', ', ', $pic);
            }
        }

        $activity->properties = $properties;
    }

    public function report()
    {
        return $this->belongsTo(WpiReport::class, 'wpi_report_id');
    }
}
