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
        Schema::create('investigation_teams', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel utama Incident Report
            $table->foreignId('incident_report_id')->constrained('incident_reports')->cascadeOnDelete();

            // Data personil tim (diambil dari searchable select Part 3)
            $table->foreignId('user_id')->constrained('users');
            $table->string('role'); // Pemimpin, Facilitator, atau Anggota
            $table->string('dept')->nullable();
            $table->string('jabatan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investigation_teams');
    }
};
