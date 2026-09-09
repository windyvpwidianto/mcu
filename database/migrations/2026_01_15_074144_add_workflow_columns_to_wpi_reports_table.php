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
        Schema::table('wpi_reports', function (Blueprint $table) {
            /// Kolom utama untuk melacak status saat ini
            $table->string('status')->default('Submitted')->after('id');

            // Untuk melacak siapa yang membuat laporan (untuk role Submitter)
            $table->foreignId('created_by')->nullable()->constrained('users')->after('status');

            // Kolom untuk Foreign Key ke Department/Contractor (untuk pengecekan Role ERM/Moderator)
            $table->foreignId('department_id')->nullable()->constrained('departments')->after('department');
            $table->foreignId('contractor_id')->nullable()->constrained('contractors')->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wpi_reports', function (Blueprint $table) {
           $table->dropColumn(['status', 'created_by', 'department_id', 'contractor_id']);
        });
    }
};
