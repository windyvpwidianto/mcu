<?php

/**
 * @property int|null $supervisor_id
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class McuParticipant extends Model
{
    protected $guarded = [];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(McuSchedule::class, 'mcu_schedule_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    // TAMBAHKAN INI: Relasi ke Dept Head / Supervisor (User)
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
    public function deptHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dept_head_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(McuResult::class);
    }
}
