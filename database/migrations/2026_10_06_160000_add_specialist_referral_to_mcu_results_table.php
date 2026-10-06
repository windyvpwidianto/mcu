<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah workflow_status menjadi VARCHAR agar dapat menampung status baru:
        // 'pending_doctor', 'need_specialist', 'pending_specialist_review', 'reviewed'
        DB::statement("ALTER TABLE mcu_results MODIFY COLUMN workflow_status VARCHAR(50) NOT NULL DEFAULT 'pending_doctor'");

        Schema::table('mcu_results', function (Blueprint $table) {
            // Kolom rujukan dokter spesialis
            $table->string('specialist_type')->nullable()->after('doctor_notes');
            $table->string('specialist_document')->nullable()->after('specialist_type');
            $table->date('specialist_consult_date')->nullable()->after('specialist_document');
            $table->text('specialist_notes')->nullable()->after('specialist_consult_date');

            // Kolom review ulang dokter (second review / re-evaluasi)
            $table->unsignedBigInteger('re_reviewed_by')->nullable()->after('specialist_notes');
            $table->timestamp('re_reviewed_at')->nullable()->after('re_reviewed_by');
            $table->text('re_review_notes')->nullable()->after('re_reviewed_at');

            $table->foreign('re_reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcu_results', function (Blueprint $table) {
            $table->dropForeign(['re_reviewed_by']);
            $table->dropColumn([
                'specialist_type',
                'specialist_document',
                'specialist_consult_date',
                'specialist_notes',
                're_reviewed_by',
                're_reviewed_at',
                're_review_notes',
            ]);
        });

        DB::statement("ALTER TABLE mcu_results MODIFY COLUMN workflow_status ENUM('pending_doctor', 'reviewed') NOT NULL DEFAULT 'pending_doctor'");
    }
};
