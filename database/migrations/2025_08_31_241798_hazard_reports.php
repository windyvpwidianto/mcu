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
        Schema::create('hazard_reports', function (Blueprint $table) {
            $table->id();

            // Relasi ke tipe bahaya
            $table->foreignId('event_type_id')->constrained('event_types')->cascadeOnDelete();
            $table->foreignId('event_sub_type_id')->nullable()->constrained('event_sub_types')->cascadeOnDelete();

            // Status pelapor (Departemen / Kontraktor)
            $table->enum('status', ['submitted', 'in_progress', 'pending', 'closed', 'cancelled'])->default('submitted');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('contractor_id')->nullable()->constrained('contractors')->nullOnDelete();

            // Penanggung jawab area/pelapor
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pelapor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('manualPelaporName')->nullable(); // path file

            // Lokasi
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('location_specific')->nullable();

            // Waktu kejadian
            $table->dateTime('tanggal')->nullable();

            // Deskripsi & dokumen
            $table->longText('description')->nullable();
            $table->string('doc_deskripsi')->nullable(); // path file
            $table->longText('immediate_corrective_action')->nullable();
            $table->string('doc_corrective')->nullable(); // path file

            // Keyword (kta/tta)
            $table->enum('key_word', ['kta', 'tta']);
            $table->foreignId('kondisi_tidak_aman_id')->nullable()->constrained('unsafe_conditions')->nullOnDelete();
            $table->foreignId('tindakan_tidak_aman_id')->nullable()->constrained('unsafe_acts')->nullOnDelete();

            // Risk matrix
            $table->foreignId('consequence_id')->nullable()->constrained('risk_consequences')->nullOnDelete();
            $table->foreignId('likelihood_id')->nullable()->constrained('likelihoods')->nullOnDelete();
            $table->string('risk_level')->nullable(); // Low, Moderate, High, Extreme

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hazard_reports');
    }
};
