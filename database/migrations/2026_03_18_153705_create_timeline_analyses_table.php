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
        Schema::create('timeline_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_report_id')->constrained()->onDelete('cascade');
            $table->json('analysis_steps'); // Menyimpan array why1, why2, dsb
            $table->integer('why_count_used');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_analyses');
    }
};
