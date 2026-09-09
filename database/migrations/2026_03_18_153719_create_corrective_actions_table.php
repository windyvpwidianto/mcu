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
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_report_id')->constrained()->onDelete('cascade');
            $table->text('action_description');
            $table->string('hierarchy'); // Eliminasi, APD, dll
            $table->foreignId('pic_user_id'); // ID dari searchable select
            $table->date('due_date');
            $table->date('actual_completion_date')->nullable();
            $table->string('status')->default('Open'); // Open / Closed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corrective_actions');
    }
};
