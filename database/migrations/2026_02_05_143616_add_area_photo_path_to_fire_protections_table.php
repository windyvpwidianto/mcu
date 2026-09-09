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
           // Kita letakkan setelah documentation_path agar rapi
            $table->string('area_photo_path')->nullable()->after('documentation_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fire_protections', function (Blueprint $table) {
           $table->dropColumn('area_photo_path');
        });
    }
};
