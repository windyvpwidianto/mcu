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
        Schema::create('likelihoods', function (Blueprint $table) {
            $table->id();
            $table->string('level')->nullable(); // contoh: 1, 2, 3, 4, 5
            $table->string('name')->nullable(); // Rare, Unlikely, Likely, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likelihoods');
    }
};
