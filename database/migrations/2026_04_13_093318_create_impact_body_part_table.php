<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impact_body_part', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel incident_impacts
            $table->foreignId('incident_impact_id')
                ->constrained('incident_impacts')
                ->onDelete('cascade');

            // Menghubungkan ke tabel body_parts
            $table->foreignId('body_part_id')
                ->constrained('body_parts')
                ->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impact_body_part');
    }
};
