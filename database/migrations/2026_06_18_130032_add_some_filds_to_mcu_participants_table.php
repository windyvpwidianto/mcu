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
        Schema::table('mcu_participants', function (Blueprint $table) {
            $table->string('spv_name_manual')->nullable();
            $table->string('dept_head_name_manual')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcu_participants', function (Blueprint $table) {
            $table->string('spv_name_manual')->nullable();
            $table->string('dept_head_name_manual')->nullable();
        });
    }
};
