<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'department_name',
        'status'
    ];
    // app/Models/Department.php

    public function incidentReports()
    {
        // Mengacu pada kolom department_id di tabel incident_reports
        return $this->hasMany(IncidentReport::class, 'department_id');
    }

    public function contractors()
    {
        return $this->belongsToMany(Contractor::class, 'custodians')->withPivot('status')
            ->wherePivot('status', 'enabled')   // jika perlu filter di pivot juga
            ->enabled();
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'department_groups');
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function scopeSearchdept($query, $term)
    {
        return $query->where('department_name',  'like', '%' . $term . '%');
    }
    public function scopeSearchCompany($query, $term)
    {
        $query->when(
            $term ?? false,
            fn($query, $term) =>
            $query->whereHas('contractors', function ($q) use ($term) {
                $q->where('contractor_id', $term);
            })
        );
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
