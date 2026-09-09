<?php

namespace App\Models;

use App\Models\Scopes\EnabledBusinessUnitScope;
use Illuminate\Database\Eloquent\Model;

class BusinessUnit extends Model
{

    protected $table = 'business_units';
    protected $fillable = [
        'company_name',
        'company_id',
        'status'
    ];
    protected static function booted()
    {
        static::addGlobalScope(new EnabledBusinessUnitScope);
    }
    public function companies()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

}
