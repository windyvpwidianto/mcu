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
        Schema::create('action_hazards', function (Blueprint $table) {
            $table->id();
            // Relasi ke hazard_reports (asumsi nama tabel hazard_reports)
            $table->foreignId('hazard_id')->constrained('hazard_reports')->cascadeOnDelete();
            // Kolom tanggal
            $table->date('original_date')->nullable();   // catatan: mungkin typo “original”?
            $table->date('due_date')->nullable();
            $table->date('actual_close_date')->nullable();
            // Deskripsi
            $table->text('description')->nullable();
            // Penanggung jawab (user)
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_hazards');
    }
};
