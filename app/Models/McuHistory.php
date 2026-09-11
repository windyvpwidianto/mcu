<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class McuHistory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'historical_date' => 'date',
    ];

    public function masterData()
    {
        return $this->belongsTo(McuMasterData::class, 'mcu_master_data_id');
    }
}
