<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionSession extends Model
{
    protected $table = 'inspection_sessions';
    protected $fillable = [
        'inspection_date', // Tambahkan ini
        'inspection_number',
        'area_photo_path',
        'submitted_by',
        'documentation_path',
        'area_name',
    ];
    protected $casts = [
        'inspection_date' => 'date',
    ];
}
