<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('risk_assessment_matrices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_assessment_id')->constrained('risk_assessments')->cascadeOnDelete();
            $table->foreignId('risk_matrix_cell_id')->constrained('risk_matrix')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_assessment_matrices');
    }
};
