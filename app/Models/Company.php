<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'company_name',
        'status'
    ];
     public function business_unit()
    {
        return $this->hasMany(BusinessUnit::class);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('company_name',  'like', '%' . $term . '%');
    }
}
