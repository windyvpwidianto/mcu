<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskAssessmentMatrix extends Model
{
   protected $table = 'risk_assessment_matrices';
    protected $fillable = [
        'risk_assessment_id',
        'risk_matrix_cell_id',
    ];

    public function riskAssessment()
    {
        return $this->belongsTo(RiskAssessment::class);
    }

    public function riskMatrixCell()
    {
        return $this->belongsTo(RiskMatrixCell::class,'risk_matrix_cell_id');
    }
}
