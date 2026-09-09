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
            // Menambahkan kolom title setelah kolom id (opsional)
            // Gunakan ->nullable() jika banyak data lama yang belum punya judul
            $table->string('title')->after('id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
