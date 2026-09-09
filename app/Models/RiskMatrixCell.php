<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskMatrixCell extends Model
{
    protected $table='risk_matrix';
    protected $fillable = [
        'likelihood_id',
        'risk_consequence_id',
        'score',
        'severity',
        'description',
        'action',
        'company_id', // opsional, jika multi perusahaan
    ];

    // 🔁 Relasi ke Likelihood
    public function likelihood()
    {
        return $this->belongsTo(Likelihood::class, 'likelihood_id');
    }

    // 🔁 Relasi ke Consequence
    public function consequence()
    {
        return $this->belongsTo(RiskConsequence::class, 'risk_consequence_id');
    }

    // 🔁 (Opsional) Relasi ke Perusahaan
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // 🔎 Scope opsional jika kamu ingin filter by perusahaan
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // 🔢 Auto generate score jika belum ada
    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->score)) {
                $likelihood = Likelihood::find($model->likelihood_id)?->level ?? 0;
                $consequence = RiskConsequence::find($model->risk_consequence_id)?->level ?? 0;
                $model->score = $likelihood * $consequence;
            }
        });
    }
}
