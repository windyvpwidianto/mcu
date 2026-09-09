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
        Schema::table('inspection_sessions', function (Blueprint $table) {
            $table->string('submitted_by')->after('inspection_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_sessions', function (Blueprint $table) {
             $table->dropColumn('submitted_by');
        });
    }
};
