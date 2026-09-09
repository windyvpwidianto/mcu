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
        Schema::create('involved_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_report_id')->constrained()->onDelete('cascade');
            $table->string('employee_name');
            $table->string('employee_nik')->nullable();
            $table->string('dept_cont')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('roster')->nullable();
            $table->string('shift')->nullable();
            $table->string('keterlibatan')->nullable();
            $table->string('pengalaman_kerja')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('involved_people');
    }
};
