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
        Schema::create('mcu_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('contractor_id')->nullable()->index();
            $table->unsignedBigInteger('mcu_record_id')->nullable()->index();
            $table->string('notification_stage'); // MCU_H30, MCU_H7, MCU_H3, OVERDUE
            $table->string('channel'); // whatsapp, email
            $table->date('scheduled_date');
            $table->string('status')->default('Pending'); // Pending, Queued, Sent, Failed, Retrying
            $table->timestamp('sent_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Anti-duplication constraints
            $table->unique(['user_id', 'notification_stage', 'scheduled_date'], 'user_notif_unique');
            $table->unique(['contractor_id', 'notification_stage', 'scheduled_date'], 'contractor_notif_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcu_notification_logs');
    }
};
