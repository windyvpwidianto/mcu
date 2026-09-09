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
        Schema::create('disease_category_mcu_result', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mcu_result_id')->constrained('mcu_results')->onDelete('cascade');
            $table->foreignId('disease_category_id')->constrained('disease_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disease_category_mcu_result');
    }
};
