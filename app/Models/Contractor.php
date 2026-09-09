<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CustodianActiveScope;
use Illuminate\Database\Eloquent\Builder;

class Contractor extends Model
{
    protected $table = 'contractors';

    protected $fillable = [
        'contractor_name',
        'status'
    ];
    public function scopeEnabled($query)
    {
        return $query->where('contractors.status', 'enabled');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'custodians');
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function scopeSearch($query, $term)
    {
        return $query->where('contractor_name',  'like', '%' . $term . '%');
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    // app/Models/Contractor.php
    public function incidentReports()
    {
        return $this->hasMany(IncidentReport::class, 'contractor_id');
    }
}
