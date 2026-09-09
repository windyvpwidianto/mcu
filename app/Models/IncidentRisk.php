<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class IncidentRisk extends Model
{
    use LogsActivity;

    protected $fillable = [
        'incident_report_id',
        'likelihood_id',
        'consequence_id',
        'rating_name',
        'deadline'
    ];

    /**
     * Konfigurasi Log Activity untuk Penilaian Risiko
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('IncidentDetail')
            ->setDescriptionForEvent(fn(string $eventName) => "Incident Risk Assessment has been {$eventName}");
    }

    /**
     * Mencegat aktivitas untuk merekam label dari Likelihood dan Consequence
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties->toArray();

        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $field => $value) {
                $labelValue = null;

                switch ($field) {
                    case 'likelihood_id':
                        $labelValue = \App\Models\Likelihood::find($value)?->name;
                        break;
                    case 'consequence_id':
                        // Sesuaikan dengan nama model RiskConsequence Anda
                        $labelValue = \App\Models\RiskConsequence::find($value)?->name;
                        break;
                }

                if ($labelValue) {
                    // Simpan label untuk nilai baru
                    $properties['attributes'][$field . '_label'] = $labelValue;

                    // Simpan label untuk nilai lama (old) jika sedang update
                    if (isset($properties['old'][$field])) {
                        $oldVal = $properties['old'][$field];
                        if ($field === 'likelihood_id') {
                            $properties['old'][$field . '_label'] = \App\Models\Likelihood::find($oldVal)?->name;
                        } elseif ($field === 'consequence_id') {
                            $properties['old'][$field . '_label'] = \App\Models\RiskConsequence::find($oldVal)?->name;
                        }
                    }
                }
            }
            $activity->properties = collect($properties);
        }
    }

    /**
     * Relasi balik ke Report
     */
    public function report()
    {
        return $this->belongsTo(IncidentReport::class, 'incident_report_id');
    }

    public function likelihood()
    {
        return $this->belongsTo(Likelihood::class);
    }

    public function consequence()
    {
        return $this->belongsTo(RiskConsequence::class);
    }
}
