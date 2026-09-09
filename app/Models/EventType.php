<?php

namespace App\Models;

use App\Models\Scopes\EnabledEventTypeScope;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $table = 'event_types';

    protected $fillable = [
        'event_category_id',
        'event_type_name',
        'status'
    ];
    protected static function booted()
    {
        static::addGlobalScope(new EnabledEventTypeScope);
    }
    public function scopeSearch($query, $term)
    {
        return $query->where('event_type_name',  'like', '%' . $term . '%');
    }
    public function scopeSearchEventCategory($query, $term)
    {
        return $query->where('event_category_id',  'like', '%' . $term . '%');
    }
    public function EventCategories()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }
    public function EventSubType()
    {
        return $this->hasMany(EventSubType::class);
    }
    public function scopeOnlyIncidents($query)
    {
        return $query->whereHas('EventCategories', function ($q) {
            $q->where('event_category_name', 'Incident');
        });
    }
    public function isInjury()
    {
        return strtolower($this->event_type_name) === 'injury';
    }
    public function isOhsHazard()
    {
        return strtolower($this->event_type_name) === 'OHS Hazard Report';
    }
    public function isEnvHazard()
    {
        return strtolower($this->event_type_name) === 'ENV Hazard Report';
    }
    public function eventSubTypes() // Tambahkan 's' dan gunakan camelCase
    {
        return $this->hasMany(EventSubType::class, 'event_type_id'); // Tambahkan foreign key eksplisit agar aman
    }
    public function incidentReports()
    {
        // Sesuaikan nama foreign key jika bukan event_type_id
        return $this->hasMany(IncidentReport::class, 'event_type_id');
    }
}
