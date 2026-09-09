<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionChecklist extends Model
{
    protected $fillable = ['equipment_type', 'location_keyword', 'location_specific', 'inputs', 'checks'];

    protected $casts = [
        'inputs' => 'array',
        'checks' => 'array',
    ];
}
