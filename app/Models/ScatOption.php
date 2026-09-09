<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScatOption extends Model
{
    protected $fillable = ['code', 'name', 'type'];

    // Helper untuk mempermudah pengambilan label lengkap
    public function getFullLabelAttribute()
    {
        return "{$this->code} {$this->name}";
    }
}
