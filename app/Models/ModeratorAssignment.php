<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeratorAssignment extends Model
{
    protected $table='moderator_assignments';
    protected $fillable = ['user_id', 'department_id', 'contractor_id', 'event_type_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class,'event_type_id');
    }
}
