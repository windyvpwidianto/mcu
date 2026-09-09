<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnsafeAct extends Model
{
    protected $table = 'unsafe_acts';
    protected $fillable = [
        'name',
        'status'
    ];
}
