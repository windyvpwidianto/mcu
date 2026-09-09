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
        Schema::create('wpi_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->time('report_time');
            $table->string('location');
            $table->string('site_name')->default('Tokatindung');
            $table->string('area')->nullable();
            $table->string('department');
            $table->json('inspectors'); // Menyimpan array Nama & ID petugas
            $table->string('reviewed_by')->nullable();
            $table->string('review_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wpi_reports');
    }
};
