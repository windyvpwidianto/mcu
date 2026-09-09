<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department_group extends Model
{
     protected $table = 'department_groups';

    protected $fillable = [
        'group_id',
        'department_id',
        'status'
    ];

    public function Departemen()
    {
        return $this->belongsTo(Department::class,'department_id');
    }
    public function Group()
    {
        return $this->belongsTo(Group::class,'group_id');
    }
}
