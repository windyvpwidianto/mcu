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
        Schema::create('compliance_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('class');
            $table->integer('duration_months')->nullable(); // Simpan dalam bulan agar fleksibel (6 bulan = 6, 1 tahun = 12, 2 tahun = 24, 3 tahun = 36 bulan, 5 tahun = 60)
            $table->string('title');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_masters');
    }
};
