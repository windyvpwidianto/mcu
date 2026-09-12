<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop mcu_histories
        Schema::dropIfExists('mcu_histories');

        // 2. Drop mcu_master_data_id from mcu_results
        if (Schema::hasColumn('mcu_results', 'mcu_master_data_id')) {
            Schema::table('mcu_results', function (Blueprint $table) {
                // If it fails to drop foreign, we just ignore or try-catch.
                // Or just drop column (Laravel 9+ drops foreign keys automatically on SQLite, but on MySQL it might need explicit drop).
                try {
                    $table->dropForeign(['mcu_master_data_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('mcu_master_data_id');
            });
        }

        // 3. Drop mcu_master_data
        Schema::dropIfExists('mcu_master_data');

        // 4. Update mcu_participants before rename (if it exists)
        if (Schema::hasTable('mcu_participants')) {
            Schema::table('mcu_participants', function (Blueprint $table) {
                $table->unsignedBigInteger('mcu_schedule_id')->nullable()->change();
                if (!Schema::hasColumn('mcu_participants', 'mcu_year')) {
                    $table->year('mcu_year')->nullable();
                }
                if (!Schema::hasColumn('mcu_participants', 'mcu_date')) {
                    $table->date('mcu_date')->nullable();
                }
                if (!Schema::hasColumn('mcu_participants', 'status')) {
                    $table->string('status')->default('Pending');
                }
            });
            
            // 5. Rename table
            Schema::rename('mcu_participants', 'mcu_records');
        }

        // 6. Rename column in mcu_results to point to mcu_record_id
        if (Schema::hasColumn('mcu_results', 'mcu_participant_id')) {
            Schema::table('mcu_results', function (Blueprint $table) {
                $table->renameColumn('mcu_participant_id', 'mcu_record_id');
            });
            
            Schema::table('mcu_results', function (Blueprint $table) {
                // Re-add foreign key pointing to mcu_records
                $table->foreign('mcu_record_id')->references('id')->on('mcu_records')->cascadeOnDelete();
            });
        }
        
        // 7. Seed existing data
        if (Schema::hasTable('mcu_records')) {
            $records = DB::table('mcu_records')
                ->join('mcu_schedules', 'mcu_records.mcu_schedule_id', '=', 'mcu_schedules.id')
                ->select('mcu_records.id', 'mcu_schedules.schedule_date')
                ->get();
                
            foreach ($records as $record) {
                if ($record->schedule_date) {
                    DB::table('mcu_records')->where('id', $record->id)->update([
                        'mcu_date' => $record->schedule_date,
                        'mcu_year' => date('Y', strtotime($record->schedule_date)),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mcu_results', 'mcu_record_id')) {
            Schema::table('mcu_results', function (Blueprint $table) {
                try {
                    $table->dropForeign(['mcu_record_id']);
                } catch (\Exception $e) {}
                $table->renameColumn('mcu_record_id', 'mcu_participant_id');
            });
        }

        if (Schema::hasTable('mcu_records')) {
            Schema::rename('mcu_records', 'mcu_participants');
            Schema::table('mcu_participants', function (Blueprint $table) {
                $table->dropColumn(['mcu_year', 'mcu_date', 'status']);
            });
        }
    }
};
