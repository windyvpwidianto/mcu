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
        Schema::create('inspection_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_type'); // Contoh: Fire Hydrant
            $table->string('location_keyword')->default('Default'); // Contoh: Maesa Camp
            $table->json('inputs'); // Simpan ['Hydrant No']
            $table->json('checks'); // Simpan ['Air', 'Kaca', dsb]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_checklists');
    }
};
