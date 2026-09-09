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
            // Kolom untuk menyimpan data tugas/tindakan cepat
            $table->text('tasks')->nullable()->after('description');

            // Kolom untuk menyimpan potensi LTI (Yes/No)
            // Kita gunakan string agar sinkron dengan value "Yes"/"No" dari radio button
            $table->string('potential_lti', 5)->nullable()->after('event_sub_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropColumn(['tasks', 'potential_lti']);
        });
    }
};
