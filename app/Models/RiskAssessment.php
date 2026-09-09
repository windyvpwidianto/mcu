<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
     protected $table = 'risk_assessments';

    protected $fillable = [
        'name',
        'action_days',
        'coordinator',
        'reporting_obligation',
        'notes',
    ];
}
