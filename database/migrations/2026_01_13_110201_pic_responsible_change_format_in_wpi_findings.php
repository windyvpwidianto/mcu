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
        Schema::table('wpi_findings', function (Blueprint $table) {
           $table->json('pic_responsible')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wpi_findings', function (Blueprint $table) {
           $table->json('pic_responsible')->change();
        });
    }
};
