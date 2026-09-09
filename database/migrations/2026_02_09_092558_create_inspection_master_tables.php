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
        // Cek tabel Master Checklist
        if (!Schema::hasTable('inspection_checklist_masters')) {
            Schema::create('inspection_checklist_masters', function (Blueprint $table) {
                $table->id();
                $table->string('equipment_type');
                $table->string('location_keyword')->default('Default');
                $table->json('inputs');
                $table->json('checks');
                $table->timestamps();
            });
        }

        // Cek tabel Sesi (Header)
        if (!Schema::hasTable('inspection_sessions')) {
            Schema::create('inspection_sessions', function (Blueprint $table) {
                $table->id();
                $table->date('inspection_date');
                $table->string('inspected_by');
                $table->string('area_name');
                $table->string('area_photo_path')->nullable();
                $table->timestamps();
            });
        }

        // Cek kolom baru di tabel fire_protections
        if (Schema::hasTable('fire_protections')) {
            Schema::table('fire_protections', function (Blueprint $table) {
                if (!Schema::hasColumn('fire_protections', 'inspection_session_id')) {
                    $table->unsignedBigInteger('inspection_session_id')->nullable()->after('id');
                }
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_master_tables');
    }
};
