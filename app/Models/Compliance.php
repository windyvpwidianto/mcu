<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compliance extends Model
{
    protected $fillable = [
    'user_id',
    'compliance_master_id',
    'start_date',
    'expired_at',
    'status',
];

public function user()
{
    return $this->belongsTo(User::class);
}

public function master()
{
    return $this->belongsTo(ComplianceMaster::class, 'compliance_master_id');
}
}
