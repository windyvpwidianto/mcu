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
        Schema::table('fire_protections', function (Blueprint $table) {
            $table->string('submitted_by')->nullable()->after('area_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fire_protections', function (Blueprint $table) {
            $table->dropColumn('submitted_by');
        });
    }
};
