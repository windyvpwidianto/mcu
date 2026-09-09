<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;

class IncidentReport extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];

    protected $casts = [
        'scat_analysis' => 'array',
        'date_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Konfigurasi Log Activity
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Incident');
    }

    /**
     * Tambahkan fungsi ini untuk mapping ID ke Nama secara otomatis
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $properties = $activity->properties->toArray();

        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $field => $value) {
                // Mapping otomatis berdasarkan nama field ID
                $label = match ($field) {
                    'event_type_id'     => \App\Models\EventType::find($value)?->name,
                    'location_id'       => \App\Models\Location::find($value)?->name,
                    'department_id'     => \App\Models\Department::find($value)?->name,
                    'contractor_id'     => \App\Models\Contractor::find($value)?->name,
                    'pelapor_id'        => \App\Models\User::find($value)?->name,
                    'penanggung_jawab'  => \App\Models\User::find($value)?->name,
                    'pm_contractor_id'  => \App\Models\User::find($value)?->name,
                    'pm_internal_id'    => \App\Models\User::find($value)?->name,
                    'ohs_head_id'       => \App\Models\User::find($value)?->name,
                    'ktt_id'            => \App\Models\User::find($value)?->name,
                    default             => null
                };

                if ($label) {
                    // Simpan label ke dalam properties log (misal: location_id_label)
                    $properties['attributes'][$field . '_label'] = $label;

                    // Jika ada data lama (old), ambil juga label lamanya
                    if (isset($properties['old'][$field])) {
                        $oldValue = $properties['old'][$field];
                        $properties['old'][$field . '_label'] = match ($field) {
                            'event_type_id'     => \App\Models\EventType::find($oldValue)?->name,
                            'location_id'       => \App\Models\Location::find($oldValue)?->name,
                            'department_id'     => \App\Models\Department::find($oldValue)?->name,
                            'contractor_id'     => \App\Models\Contractor::find($oldValue)?->name,
                            'pelapor_id'        => \App\Models\User::find($oldValue)?->name,
                            'penanggung_jawab'  => \App\Models\User::find($oldValue)?->name,
                            'pm_contractor_id'  => \App\Models\User::find($oldValue)?->name,
                            'pm_internal_id'    => \App\Models\User::find($oldValue)?->name,
                            'ohs_head_id'       => \App\Models\User::find($oldValue)?->name,
                            'ktt_id'            => \App\Models\User::find($oldValue)?->name,
                            default             => null
                        } ?? $oldValue;
                    }
                }
            }
            $activity->properties = collect($properties);
        }
    }

    /**
     * RELASI AKTIVITAS
     */

    // Relasi standar (Hanya log milik IncidentReport itu sendiri)
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    // Relasi kustom (Menggabungkan log header + log milik tabel anak)
    public function allActivities()
    {
        return Activity::where(function ($query) {
            // 1. Ambil log yang subjek utamanya adalah IncidentReport ini sendiri
            $query->where('subject_type', get_class($this))
                ->where('subject_id', $this->id);
        })->orWhere(function ($query) {
            // 2. Ambil log dari tabel relasi (anak) yang menyimpan referensi ke Incident ini
            // Kita periksa di dalam attributes (data baru) dan old (data lama)
            $query->where('properties->attributes->incident_report_id', $this->id)
                ->orWhere('properties->old->incident_report_id', $this->id)
                // Tambahan: Kadang Spatie menyimpan ID di root properties tergantung konfigurasi tapActivity
                ->orWhere('properties->incident_report_id', $this->id);
        })->latest();
    }

    /**
     * ==========================================
     * RELASI HEADER (BELONGS TO)
     * ==========================================
     */

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }
    public function eventSubType(): BelongsTo
    {
        return $this->belongsTo(EventSubType::class, 'event_sub_type_id');
    }
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penanggung_jawab');
    }
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }


    /**
     * ==========================================
     * RELASI APPROVAL (PART 9)
     * ==========================================
     */

    public function pmContractor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pm_contractor_id');
    }
    public function pmInternal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pm_internal_id');
    }
    public function ohsHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ohs_head_id');
    }
    public function ktt(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ktt_id');
    }

    /**
     * ==========================================
     * RELASI DETAIL (HAS MANY / HAS ONE)
     * ==========================================
     */

    public function impact(): HasOne
    {
        return $this->hasOne(IncidentImpact::class);
    }
    public function involvedPersons(): HasMany
    {
        return $this->hasMany(InvolvedPerson::class);
    }
    public function investigationTeams(): HasMany
    {
        return $this->hasMany(InvestigationTeam::class);
    }
    public function peepoAnalyses(): HasMany
    {
        return $this->hasMany(PeepoAnalysis::class);
    }
    public function timelines(): HasMany
    {
        return $this->hasMany(TimelineAnalysis::class, 'incident_report_id');
    }
    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class);
    }
    public function attachments(): HasMany
    {
        return $this->hasMany(IncidentAttachment::class);
    }
    public function risk(): HasOne
    {
        return $this->hasOne(IncidentRisk::class, 'incident_report_id');
    }

    /**
     * ==========================================
     * ACCESSORS
     * ==========================================
     */

    public function getLatestReviewerNameAttribute()
    {
        if ($this->ktt_id) return $this->ktt?->name;
        if ($this->ohs_head_id) return $this->ohsHead?->name;
        if ($this->pm_internal_id) return $this->pmInternal?->name;
        if ($this->pm_contractor_id) return $this->pmContractor?->name;
        return 'Waiting Review';
    }

    public function getLatestReviewerRoleAttribute()
    {
        if ($this->ktt_id) return 'KTT';
        if ($this->ohs_head_id) return 'OHS Head';
        if ($this->pm_internal_id) return 'PM Internal';
        if ($this->pm_contractor_id) return 'PM Contractor';
        return 'Pending';
    }
    public function getAssignedModerators()
    {
        return User::whereHas('moderatorAssignments', function (Builder $query) {
            $query->where('event_type_id', $this->event_type_id)
                ->where(function (Builder $subQuery) {
                    // Kriteria A: Moderator Global (Dept & Contractor NULL)
                    $subQuery->where(function ($q) {
                        $q->whereNull('department_id')
                            ->whereNull('contractor_id');
                    });

                    // Kriteria B: Moderator spesifik Department
                    if ($this->department_id) {
                        $subQuery->orWhere('department_id', $this->department_id);
                    }

                    // Kriteria C: Moderator spesifik Contractor
                    if ($this->contractor_id) {
                        $subQuery->orWhere('contractor_id', $this->contractor_id);
                    }
                });
        })->get();
    }
}
