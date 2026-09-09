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
        Schema::table('incident_reports', function (Blueprint $table) {
            // Kita letakkan setelah kolom nomor laporan agar rapi di database
            $table->enum('status', ['Open', 'In Progress', 'Action Required', 'Closed', 'Waiting Review'])->default('Open')->after('report_number');

            // Index ditambahkan untuk mempercepat filter di dashboard SENTRY
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
