<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskConsequence extends Model
{
    protected $table = 'risk_consequences';

    protected $fillable = [
        'level',
        'name',
        'description',
    ];
}
