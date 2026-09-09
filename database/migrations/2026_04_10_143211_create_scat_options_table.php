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
        Schema::create('scat_options', function (Blueprint $table) {
            $table->id();
            $table->string('code'); // Contoh: 1.2.1
            $table->string('name'); // Contoh: Mengoperasikan peralatan tanpa izin
            $table->enum('type', ['unsafe_act', 'personal_factor', 'job_factor', 'control_system']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scat_options');
    }
};
