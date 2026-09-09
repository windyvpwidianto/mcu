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
            // Menambahkan kolom Area Kontrak Karya
            $table->string('contract_area_name')->nullable()->after('location_id');

            // Menambahkan kolom Klasifikasi Lingkungan
            $table->string('env_classification')->nullable()->after('contract_area_name');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropColumn(['contract_area_name', 'env_classification']);
        });
    }
};
