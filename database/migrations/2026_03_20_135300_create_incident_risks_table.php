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
        Schema::create('incident_risks', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel utama (Incident Report)
            $table->foreignId('incident_report_id')->constrained()->onDelete('cascade');

            // Data Risk Matrix
            $table->foreignId('consequence_id')->nullable()->constrained('risk_consequences')->nullOnDelete();
            $table->foreignId('likelihood_id')->nullable()->constrained('likelihoods')->nullOnDelete();

            // Data tambahan (denormalized dari tabel master RiskAssessment)
            $table->string('rating_name')->nullable(); // Misal: High, Medium, Low
            $table->text('deadline')->nullable();    // Misal: 1x24 Jam
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_risks');
    }
};
