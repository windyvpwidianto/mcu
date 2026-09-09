<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentDraft extends Model
{
    protected $fillable = ['user_id', 'payload'];

    // CRITICAL: Agar array di Livewire tersimpan sebagai JSON yang benar
    protected $casts = [
        'payload' => 'array',
    ];
}
