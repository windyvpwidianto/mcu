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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->text('key');      // Contoh: "Nama Perusahaan" atau "company_name"
            $table->text('en');         // Contoh: "Company Name"
            $table->text('id_text');         // Contoh: "Nama Perusahaan"
            $table->string('group');    // Untuk mengelompokkan (misal: 'form', 'report')
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
