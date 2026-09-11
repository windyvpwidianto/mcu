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
        Schema::table('mcu_results', function (Blueprint $table) {
            $table->foreignId('mcu_master_data_id')->nullable()->constrained('mcu_master_data')->cascadeOnDelete();
            $table->unsignedBigInteger('mcu_participant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcu_results', function (Blueprint $table) {
            $table->dropForeign(['mcu_master_data_id']);
            $table->dropColumn('mcu_master_data_id');
        });
    }
};
