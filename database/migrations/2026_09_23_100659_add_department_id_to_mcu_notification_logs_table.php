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
        Schema::table('mcu_notification_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->index()->after('contractor_id');
            $table->unique(['department_id', 'notification_stage', 'scheduled_date'], 'dept_notif_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcu_notification_logs', function (Blueprint $table) {
            $table->dropUnique('dept_notif_unique');
            $table->dropColumn('department_id');
        });
    }
};
