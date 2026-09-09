<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\UnsafeAct;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;

class Hazard extends Model
{
    use LogsActivity;

    protected $table = 'hazard_reports';

    protected $fillable = [
        'no_referensi',
        'event_type_id',
        'event_sub_type_id',
        'status',
        'department_id',
        'contractor_id',
        'penanggung_jawab_id',
        'pelapor_id',
        'manualPelaporName',
        'location_id',
        'location_specific',
        'tanggal',
        'description',
        'moderator_comment',
        'doc_deskripsi',
        'immediate_corrective_action',
        'doc_corrective',
        'key_word',
        'kondisi_tidak_aman_id',
        'tindakan_tidak_aman_id',
        'consequence_id',
        'likelihood_id',
        'risk_level',
    ];

    protected static function boot()
    {
        parent::boot();

        // Mendengarkan event 'deleting'
        static::deleting(function ($hazard) {
            // Hapus file dokumentasi sesudah perbaikan jika ada
            if ($hazard->doc_corrective && Storage::disk('public')->exists($hazard->doc_corrective)) {
                Storage::disk('public')->delete($hazard->doc_corrective);
            }

            // Jika ada file dokumentasi lainnya, tambahkan logika penghapusan di sini.
            // Contoh:
            if ($hazard->doc_deskripsi && Storage::disk('public')->exists($hazard->doc_deskripsi)) {
                Storage::disk('public')->delete($hazard->doc_deskripsi);
            }
        });
    }

    protected static $logOnlyDirty = true;
    protected static $logName = 'hazard_report';

    /**
     * Activity Log Options
     */
    public function getActivitylogOptions(): LogOptions
    {
        // Catat semua field, tapi log *_id diganti nama lewat accessor
        return LogOptions::defaults()
            ->useLogName($this->getTable())
            ->logAll()
            ->logOnlyDirty();
    }

    /**
     * Tap activity untuk menambahkan nama relasi
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $map = [
            'penanggung_jawab_id' => fn($id) => $id ? $this->penanggungJawab?->name : null,
            'pelapor_id'          => fn($id) => $id ? $this->pelapor?->name : null,
            'department_id'       => fn($id) => $id ? $this->department?->department_name : null,
            'contractor_id'       => fn($id) => $id ? $this->contractor?->contractor_name : null,
            'location_id'         => fn($id) => $id ? $this->location?->name : null,
            'event_type_id'         => fn($id) => $id ? $this->eventType?->event_type_name : null,
            'event_sub_type_id'         => fn($id) => $id ? $this->eventSubType?->event_sub_type_name : null,
        ];

        foreach (['attributes', 'old'] as $key) {
            if (!isset($activity->properties[$key])) continue;

            $props = collect($activity->properties[$key]);
            foreach ($map as $field => $resolver) {
                if (isset($props[$field])) {
                    // Ganti ID dengan name agar log lebih readable
                    $props[$field . '_name'] = $resolver($props[$field]);
                }
            }
            $activity->properties[$key] = $props->toArray();
        }
    }

    /** RELATIONS */
    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }
    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }
    public function eventSubType()
    {
        return $this->belongsTo(EventSubType::class, 'event_sub_type_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }
    public function penanggungJawab()
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }
    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function consequence()
    {
        return $this->belongsTo(RiskConsequence::class);
    }
    public function likelihood()
    {
        return $this->belongsTo(Likelihood::class);
    }
    public function assignedErms()
    {
        return $this->belongsToMany(User::class, 'hazard_erm_assignments', 'hazard_id', 'erm_id');
    }
    public function actionHazards()
    {
        return $this->hasMany(ActionHazard::class, 'hazard_id');
    }
    public function hazardKondisiTidakAman()
    {
        return $this->belongsTo(UnsafeCondition::class, 'kondisi_tidak_aman_id');
    }
    public function hazardTindakanTidakAman()
    {
        return $this->belongsTo(UnsafeAct::class, 'tindakan_tidak_aman_id');
    }
    /** SCOPES */
    public function scopeStatus($query, $status)
    {
        // 1. Periksa apakah $status adalah array dan apakah array tersebut tidak kosong.
        if (!empty($status)) {

            // 2. Tambahkan pemeriksaan tambahan jika $status bukan array (hanya untuk keamanan)
            if (!is_array($status)) {
                $status = [$status];
            }

            // 3. Hanya tambahkan whereIn jika ada status yang dipilih
            return $query->whereIn('status', $status);
        }

        // 4. Jika $status kosong, jangan lakukan apa-apa (kembalikan $query asli)
        return $query;
    }

    public function scopeByEventType($query, $id)
    {
        return $query->where('event_type_id', $id);
    }

    public function scopeByDepartment($query, $name)
    {
        return $query->whereHas('department', function ($q) use ($name) {
            $q->where('department_name', 'like', "%{$name}%");
        });
    }

    public function scopeByContractor($query, $name)
    {
        return $query->whereHas('contractor', function ($q) use ($name) {
            $q->where('contractor_name', 'like', "%{$name}%");
        });
    }
    public function scopeByPelapor($query, $name)
    {
        return $query->whereHas('pelapor', function ($q) use ($name) {
            $q->where('name', 'like', "%{$name}%");
        });
    }
    public function scopeByDepartments(Builder $query, array $departmentIds): Builder
    {
        // Hanya terapkan whereIn jika array ID tidak kosong.
        if (empty($departmentIds)) {
            return $query;
        }

        return $query->whereIn('department_id', $departmentIds);
    }
    public function scopeByEventSubType(Builder $query, array $eventSubType): Builder
    {
        // Hanya terapkan whereIn jika array ID tidak kosong.
        if (empty($eventSubType)) {
            return $query;
        }

        return $query->whereIn('event_sub_type_id', $eventSubType);
    }

    /**
     * Scope untuk memfilter berdasarkan Contractor ID yang dipilih.
     *
     * @param Builder $query
     * @param array $contractorIds Array ID Contractor yang dicentang.
     * @return Builder
     */
    public function scopeByContractors(Builder $query, array $contractorIds): Builder
    {
        if (empty($contractorIds)) {
            return $query;
        }
        return $query->whereIn('contractor_id', $contractorIds);
    }
    public function scopeDateRange(Builder $query, string $startDate, string $endDate): void
    {
        if (!is_null($startDate) && !is_null($endDate)) {
            $startDateFormatted = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateFormatted = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
            $query->whereDate('tanggal', '>=', $startDateFormatted)->whereDate('tanggal', '<=', $endDateFormatted)->get();
        }
    }


    public function scopeWithHazardCounts($query)
    {
        $query->withCount([
            'actionHazards as total_due_dates' => function ($q) {
                $q->whereNotNull('due_date');
            },
            'actionHazards as pending_actual_closes' => function ($q) {
                $q->whereNull('actual_close_date');
            }
        ]);
    }

    /** HELPERS */
    public function resolveCompanyId()
    {
        return $this->department->company_id ?? $this->contractor->company_id ?? null;
    }
    public function scopeOhsOnly($query)
    {
        return $query->whereHas('eventType', function ($q) {
            $q->whereRaw('LOWER(event_type_name) = ?', ['ohs hazard report']);
        });
    }

    public function chats()
    {
        // Tambahkan parameter kedua untuk menentukan nama kolom foreign key yang benar
        return $this->hasMany(HazardChat::class, 'hazard_report_id')->orderBy('created_at', 'asc');
    }
    public function isModerator($userId = null): bool
    {
        // Jika ID tidak dikirim, ambil ID user yang sedang login
        $userId = $userId ?? auth()->id();

        if (!$userId) {
            return false;
        }

        return ModeratorAssignment::where('user_id', $userId)
            ->where('event_type_id', $this->event_type_id)
            ->where(function ($query) {
                // Kriteria 1: Penugasan Umum
                $query->where(function ($q) {
                    $q->whereNull('department_id')
                        ->whereNull('contractor_id');
                })
                    // Kriteria 2: Spesifik Department
                    ->orWhere(function ($q) {
                        if ($this->department_id) {
                            $q->where('department_id', $this->department_id);
                        } else {
                            $q->whereRaw('0 = 1'); // Paksa false jika hazard tidak punya dept
                        }
                    })
                    // Kriteria 3: Spesifik Contractor
                    ->orWhere(function ($q) {
                        if ($this->contractor_id) {
                            $q->where('contractor_id', $this->contractor_id);
                        } else {
                            $q->whereRaw('0 = 1');
                        }
                    });
            })
            ->exists();
    }
}
