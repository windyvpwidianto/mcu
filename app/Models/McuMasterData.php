<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class McuMasterData extends Model
{
    use Notifiable;

    protected $table = 'mcu_master_data';

    protected $fillable = [
        'employee_name',
        'nik',
        'company',
        'position',
        'birth_date',
        'ktp_number',
        'hp_number',
        'mcu_date',
        'notification_status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'mcu_date' => 'date',
    ];
}
