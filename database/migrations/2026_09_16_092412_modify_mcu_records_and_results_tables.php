<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mcu_records', function (Blueprint $table) {
            $table->string('attendance_status')->default('scheduled')->after('notification_status');
            $table->string('process_status')->default('scheduled')->after('attendance_status');
        });

        DB::table('mcu_records')->update([
            'attendance_status' => DB::raw("CASE WHEN is_attended = 1 THEN 'present' WHEN is_attended = 0 THEN 'absent' ELSE 'scheduled' END"),
            'process_status' => DB::raw("CASE WHEN status = 'Pending' THEN 'scheduled' ELSE status END")
        ]);

        Schema::table('mcu_records', function (Blueprint $table) {
            $table->dropColumn(['is_attended', 'status']);
        });

        Schema::table('mcu_results', function (Blueprint $table) {
            // Change enum to string for flexibility
            $table->string('status')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mcu_records', function (Blueprint $table) {
            $table->boolean('is_attended')->nullable();
            $table->string('status')->default('Pending');
        });

        DB::table('mcu_records')->update([
            'is_attended' => DB::raw("CASE WHEN attendance_status = 'present' THEN 1 WHEN attendance_status = 'absent' THEN 0 ELSE NULL END"),
            'status' => DB::raw("CASE WHEN process_status = 'scheduled' THEN 'Pending' ELSE process_status END")
        ]);

        Schema::table('mcu_records', function (Blueprint $table) {
            $table->dropColumn(['attendance_status', 'process_status']);
        });
    }
};

