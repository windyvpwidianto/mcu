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
        Schema::create('peepo_analyses', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel utama Incident Report
            $table->foreignId('incident_report_id')->constrained()->onDelete('cascade');

            // Kolom data PEEPO
            $table->string('factor_key');   // Menyimpan: P, E, E, P, atau O
            $table->string('factor_name');  // Menyimpan: People, Equipment, Environment, dll.
            $table->string('temuan')->nullable();
            $table->text('deskripsi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peepo_analyses');
    }
};
