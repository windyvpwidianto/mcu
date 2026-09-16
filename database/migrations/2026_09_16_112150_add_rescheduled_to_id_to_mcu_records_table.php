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
        Schema::table('mcu_records', function (Blueprint $table) {
            $table->unsignedBigInteger('rescheduled_to_id')->nullable()->after('process_status');
            $table->foreign('rescheduled_to_id')->references('id')->on('mcu_records')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcu_records', function (Blueprint $table) {
            $table->dropForeign(['rescheduled_to_id']);
            $table->dropColumn('rescheduled_to_id');
        });
    }
};
