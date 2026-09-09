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
            $table->string('inspection_number')->nullable()->after('submitted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fire_protections', function (Blueprint $table) {
            $table->dropColumn('inspection_number');
        });
    }
};
