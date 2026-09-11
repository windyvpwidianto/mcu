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
        Schema::create('mcu_master_data', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('nik');
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('ktp_number')->nullable();
            $table->string('hp_number')->nullable();
            $table->date('mcu_date')->nullable();
            $table->enum('notification_status', [
                'pending',
                'h-2_bulan',
                'h-1_bulan',
                'h-1_minggu',
            ])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcu_master_data');
    }
};
