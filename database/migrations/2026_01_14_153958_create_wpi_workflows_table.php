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
        Schema::create('wpi_workflows', function (Blueprint $table) {
           $table->id();
            $table->string('role'); // Submitter, Event Report Manager, Moderator
            $table->string('from_status'); // Nama Step Asal
            $table->string('to_status');   // Nama Destination
            $table->string('from_inisial'); // Label Asal
            $table->string('to_inisial');   // Label Tombol/Aksi
            $table->boolean('validate_transition')->default(false); // Kolom "Validate Transition" di gambar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wpi_workflows');
    }
};
