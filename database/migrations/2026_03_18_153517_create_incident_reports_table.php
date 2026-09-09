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
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            // Part 1 & 2: Basic Info
            $table->string('report_number')->unique();
            $table->foreignId('event_type_id')->constrained('event_types')->cascadeOnDelete();
            $table->foreignId('event_sub_type_id')->nullable()->constrained('event_sub_types')->cascadeOnDelete();
            $table->dateTime('date_time');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->string('location_specific')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('contractor_id')->nullable()->constrained('contractors')->nullOnDelete();
            $table->foreignId('penanggungJawab')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pelapor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('manual_pelapor')->nullable();
            $table->text('description'); // 5W1H
            $table->text('emergency_action')->nullable();
            // Part 8: Key Learning
            $table->text('key_learning')->nullable();

            // Part 9: Approval Comments
            $table->text('pm_contractor_comment')->nullable();
            $table->foreignId('pm_contractor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('pm_internal_comment')->nullable();
            $table->foreignId('pm_internal_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('ohs_head_comment')->nullable();
            $table->foreignId('ohs_head_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('ktt_comment')->nullable();
            $table->foreignId('ktt_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
