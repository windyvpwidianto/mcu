<?php

namespace App\Models;

use App\Models\Scopes\EnabledEventSubTypeScope;
use Illuminate\Database\Eloquent\Model;

class EventSubType extends Model
{
    protected $table = 'event_sub_types';

    protected $fillable = [
        'event_type_id',
        'event_sub_type_name',
        'status'
    ];
    protected static function booted()
    {
        static::addGlobalScope(new EnabledEventSubTypeScope);
    }
    public function EventType()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }
    public function scopeSearch($query, $term)
    {
        return $query->where('event_sub_type_name',  'like', '%' . $term . '%');
    }
    public function scopeSearchEventType($query, $term)
    {
        return $query->where('event_type_id',  'like', '%' . $term . '%');
    }
    public function scopeOnlyIncidents($query)
    {
        // Menggunakan dot notation untuk relasi yang dalam
        return $query->whereRelation('EventType.EventCategories', 'event_category_name', 'Incident');
    }
    public function scopeByEventType($q, $t)
    {
        return $q->whereHas('EventType', function ($q) use ($t) {
            $q->where('event_type_name', 'like', "%{$t}%");
        });
    }
    public function incidentReports()
    {
        // Sesuaikan nama foreign key jika bukan event_sub_type_id
        return $this->hasMany(IncidentReport::class, 'event_sub_type_id');
    }
}
