<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FireProtection extends Model
{
    protected $fillable = [
        'inspection_session_id', // Tambahkan ini
        'equipment_master_id',
        'conditions',
        'remarks',
        'documentation_path',
        'inspected_by',
    ];
    protected $casts = [
        'conditions' => 'array',
        'inspected_by' => 'array',
    ];
    public function inspectionSession()
    {
        return $this->belongsTo(InspectionSession::class, 'inspection_session_id');
    }
    public function getAreaPhotoAttribute()
    {
        // Mengambil area_photo_path dari tabel inspection_sessions
        return $this->inspectionSession ? $this->inspectionSession->area_photo_path : null;
    }
    public function equipmentMaster()
    {
        return $this->belongsTo(EquipmentMaster::class);
    }


    public function scopeSearchInstectionsByDate($query, $date)
    {
        if ($date) {
            $date = Carbon::parse($date);
            $currentMonth = $date->month; //
            // Gunakan $query, bukan $this
            return $query->whereHas('inspectionSession', function ($q) use ($currentMonth, $date) {
                $q->whereMonth('inspection_date', $currentMonth)
                    ->whereYear('inspection_date', $date->year);
            });
        }
        return $query;
    }
    public function scopeSearchInstectionsByMonth($query, $date)
    {
        if ($date) {
            $date = Carbon::parse($date);
            $currentMonth = $date->month; //
            // Gunakan $query, bukan $this
            return $query->whereHas('inspectionSession', function ($q) use ($currentMonth, $date) {
                $q->whereMonth('inspection_date', $currentMonth);
            });
        }
        return $query;
    }
    public function scopeSearchByLocation($query, $locationId)
    {
        if ($locationId) {
            return $query->whereHas('equipmentMaster', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }
        return $query;
    }
    public function scopeSearchByType($query, $type)
    {
        if ($type) {
            return $query->whereHas('equipmentMaster', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }
        return $query;
    }
}
