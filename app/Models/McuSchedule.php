<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McuSchedule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(McuParticipant::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
