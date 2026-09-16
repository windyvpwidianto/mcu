<?php

/**
 * @property int|null $supervisor_id
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class McuRecord extends Model
{
    use LogsActivity;

    protected $table = 'mcu_records';
    protected $guarded = [];

    // Attendance Statuses
    public const ATTENDANCE_SCHEDULED = 'scheduled';
    public const ATTENDANCE_PRESENT = 'present';
    public const ATTENDANCE_ABSENT = 'absent';
    public const ATTENDANCE_RESCHEDULED = 'rescheduled';
    public const ATTENDANCE_CANCELLED = 'cancelled';

    // Process Statuses
    public const PROCESS_SCHEDULED = 'scheduled';
    public const PROCESS_COMPLETED = 'completed';
    public const PROCESS_NO_SHOW = 'no_show';
    public const PROCESS_RESCHEDULED = 'rescheduled';
    public const PROCESS_EXPIRED = 'expired';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->useLogName('mcu')
            ->setDescriptionForEvent(fn(string $eventName) => "MCU Record has been {$eventName}")
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
