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
        Schema::create('hazard_erm_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_id')->constrained('hazard_reports')->onDelete('cascade');
            $table->foreignId('erm_id')->constrained('users')->onDelete('cascade');
            $table->unique(['hazard_id', 'erm_id']); // biar gak dobel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hazard_erm_assignments');
    }
};
