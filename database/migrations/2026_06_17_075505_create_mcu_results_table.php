<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcu_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mcu_participant_id')->constrained()->cascadeOnDelete();

            // 1. Tambahkan kolom untuk dokumen yang diupload admin
            $table->string('result_document')->nullable();
            $table->text('admin_notes')->nullable();

            // 2. Tambahkan status alur kerja (workflow)
            $table->enum('workflow_status', [
                'pending_doctor',
                'reviewed'
            ])->default('pending_doctor');

            // 3. Status Medis dari Dokter
            $table->enum('status', [
                'fit_to_work',
                'fit_with_notes',
                'temporary_unfit',
                'unfit'
            ])->nullable();

            $table->text('doctor_site_consult')->nullable(); // Untuk Catatan Batasan Kerja
            $table->date('follow_up_date')->nullable();      // Untuk Tanggal Follow Up

            // --- TAMBAHKAN 3 BARIS INI ---
            $table->text('doctor_notes')->nullable();        // Catatan umum tambahan dari dokter
            $table->foreignId('reviewed_by')->nullable()->constrained('users'); // ID Dokter yang review
            $table->timestamp('reviewed_at')->nullable();    // Waktu ketika dokter selesai review
            // ------------------------------

            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcu_results');
    }
};
