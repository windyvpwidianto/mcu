<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;

class WpiReport extends Model
{
    use LogsActivity;

    protected $fillable = [
        'report_date',
        'no_referensi',
        'report_time',
        'location',
        'department',
        'inspectors',
        'site_name',
        'area',
        'status',
        'reviewed_by',
        'review_id',
        'review_date',
        'created_by',
        'department_id',
        'contractor_id'
    ];

    protected $casts = [
        'inspectors' => 'array',
        'report_date' => 'date'
    ];

    /**
     * Activity Log Configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->getTable())
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Memanipulasi properti activity log sebelum disimpan agar ID
     * berubah menjadi Nama yang dapat dibaca.
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $map = [
            'created_by'    => fn($id) => $id ? User::find($id)?->name : 'System',
            'department_id' => fn($id) => $id ? $this->department_rel?->department_name : null,
            'contractor_id' => fn($id) => $id ? $this->contractor_rel?->contractor_name : null,
        ];

        $properties = $activity->properties->toArray();

        foreach (['attributes', 'old'] as $key) {
            if (!isset($properties[$key])) continue;

            // 1. Resolving Foreign IDs (Created By, Dept, Cont)
            foreach ($map as $field => $resolver) {
                if (isset($properties[$key][$field])) {
                    $properties[$key][$field . '_label'] = $resolver($properties[$key][$field]);
                }
            }

            // 2. Resolving JSON Inspectors Array
            if (isset($properties[$key]['inspectors'])) {
                $inspectors = $properties[$key]['inspectors'];
                if (is_array($inspectors)) {
                    $names = collect($inspectors)->pluck('name')->filter()->implode(', ');
                    $properties[$key]['inspectors_label'] = $names ?: '-';
                }
            }
        }

        $activity->properties = $properties;
    }

    /** * RELATIONS
     */
    // Contoh di file app/Models/FireProtection.php (sesuai gambar 2)

    public function creator()
    {
        // Mengaitkan kolom 'created_by' atau 'user_id' di tabel laporan ke id di tabel users
        return $this->belongsTo(User::class, 'created_by');
    }
    public function assignedErms(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wpi_report_user_pivot', 'wpi_report_id', 'user_id')
            ->withTimestamps();
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(WpiFinding::class);
    }

    public function department_rel(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function contractor_rel(): BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'contractor_id');
    }

    /**
     * HELPERS
     */

    public function isClosed(): bool
    {
        return in_array(strtolower($this->status), ['closed', 'cancelled']);
    }
}
