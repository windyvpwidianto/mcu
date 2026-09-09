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
        Schema::create('risk_matrix', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('likelihood_id')->nullable();
            $table->foreign('likelihood_id')->references('id')->on('likelihoods')->onDelete('cascade');
            $table->unsignedBigInteger('risk_consequence_id')->nullable();
            $table->foreign('risk_consequence_id')->references('id')->on('risk_consequences')->onDelete('cascade');
            $table->integer('score')->nullable(); // optional
            $table->enum('severity', ['Low', 'Moderate', 'High', 'Extreme'])->nullable();
            $table->text('description')->nullable(); // deskripsi dampak
            $table->text('action')->nullable();      // mitigasi, rekomendasi, dsb

            // Optional jika multi perusahaan / lokasi
            $table->foreignId('company_id')->nullable()->constrained();

            $table->timestamps();

            $table->unique(['likelihood_id', 'risk_consequence_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_matrix_cells');
    }
};
