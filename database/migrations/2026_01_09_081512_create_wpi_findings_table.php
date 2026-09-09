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
        Schema::create('wpi_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wpi_report_id')->constrained()->onDelete('cascade');
            $table->char('ohs_risk', 1);
            $table->text('description');
            $table->text('prevention_action');
            $table->json('pic_responsible')->nullable();
            $table->date('due_date');
            $table->date('completion_date')->nullable();
            $table->json('photos')->nullable(); // Path lokasi foto
            $table->json('photos_prevention')->nullable(); // Path lokasi foto tindakan pencegahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wpi_findings');
    }
};
