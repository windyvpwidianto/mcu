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
        Schema::create('incident_impacts', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel utama
            $table->foreignId('incident_report_id')->constrained('incident_reports')->onDelete('cascade');

            // Menentukan apakah ini Cedera (Injury) atau Kerusakan (Damage)
            $table->boolean('is_injury')->default(false);

            // Relasi ke tabel master body_parts yang Anda miliki (Gambar phpMyAdmin Anda)
            $table->unsignedBigInteger('body_part_id')->nullable();

            // Jika bukan injury, simpan detail kerusakan di sini (Misal: Alat berat rusak)
            $table->text('damage_detail')->nullable();

            $table->timestamps();

            // Foreign Key ke tabel body_parts
            $table->foreign('body_part_id')->references('id')->on('body_parts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_impacts');
    }
};
