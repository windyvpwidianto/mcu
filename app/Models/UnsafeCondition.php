<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnsafeCondition extends Model
{
    protected $table='unsafe_conditions';
    protected $fillable=[
        'name',
        'status'
    ];
}
