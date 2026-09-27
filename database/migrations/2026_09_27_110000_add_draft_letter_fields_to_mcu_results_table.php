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
            $table->longText('letter_content')->nullable()->after('certificate_path');
            $table->string('letter_status')->default('draft')->after('letter_content');
            $table->foreignId('letter_updated_by')->nullable()->after('letter_status')->constrained('users')->nullOnDelete();
            $table->timestamp('letter_updated_at')->nullable()->after('letter_updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcu_results', function (Blueprint $table) {
            $table->dropForeign(['letter_updated_by']);
            $table->dropColumn(['letter_content', 'letter_status', 'letter_updated_by', 'letter_updated_at']);
        });
    }
};
