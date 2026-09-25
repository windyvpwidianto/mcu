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
        Schema::table('mcu_results', function (Blueprint $table) {
            $table->string('certificate_number')->nullable()->after('status');
            $table->string('certificate_path')->nullable()->after('certificate_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcu_results', function (Blueprint $table) {
            $table->dropColumn(['certificate_number', 'certificate_path']);
        });
    }
};
