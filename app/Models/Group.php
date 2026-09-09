<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';

    protected $fillable = [
        'group_name',
    ];
    public function departments()
    {
         $ownerIds = Department::where('status', 'enabled')->pluck('id')->toArray();
        return $this->belongsToMany(Department::class, 'department_groups')->whereIn('department_id', $ownerIds);

    }
    public function scopeSearch($query, $term)
    {
        return $query->where('group_name',  'like', '%' . $term . '%');
    }
}
