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
        Schema::create('equipment_masters', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Fire Extinguisher, Fire Hydrant, dll.
            $table->foreignId('location_id')->constrained('locations'); // Relasi ke tabel area/lokasi
            $table->string('specific_location')->nullable(); // Detail lokasi (misal: "Depan Office")

            // Technical Data disimpan dalam JSON agar fleksibel
            // Isinya: {"FE No": "PH001", "FE Type": "DCP", "Capacity": "6 Kg"}
            $table->json('technical_data');

            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_masters');
    }
};
