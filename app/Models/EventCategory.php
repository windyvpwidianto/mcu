<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
     protected $table = 'event_categories';

    protected $fillable = [
        'event_category_name',
        'status'
    ];
    public function scopeSearch($query, $term)
    {
        return $query->where('event_category_name',  'like', '%' . $term . '%');
    }
     public function EventType()
    {
        return $this->hasMany(EventType::class)->where('status', 'enabled');
    }
        public function scopeStatus($query)
    {
        $query->when(
            fn($query) => $query->where('status', 'enabled')
        );
    }
}
