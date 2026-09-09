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
        Schema::create('mcu_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mcu_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users'); // Karyawan
            $table->foreignId('supervisor_id')->constrained('users');
            $table->string('whatsapp_number')->nullable();
            $table->string('spv_wa_number')->nullable();
            // Status Tracking Notifikasi
            $table->enum('notification_status', [
                'pending',
                'notified',
                'reminder_1',
                'reminder_2',
                'final_reminder'
            ])->default('pending');

            // Kehadiran (Hari H)
            $table->boolean('is_attended')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcu_participants');
    }
};
