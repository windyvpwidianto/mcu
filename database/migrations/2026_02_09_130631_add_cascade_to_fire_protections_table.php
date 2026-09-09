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
        // Kita gunakan try-catch atau pengecekan manual agar tidak error jika key tidak ada
        try {
            $table->dropForeign(['inspection_session_id']);
        } catch (\Exception $e) {
            // Abaikan jika tidak ada foreign key untuk dihapus
        }

        // Tambahkan/Update foreign key dengan cascade
        $table->foreign('inspection_session_id')
              ->references('id')
              ->on('inspection_sessions')
              ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('fire_protections', function (Blueprint $table) {
        $table->dropForeign(['inspection_session_id']);

        // Kembalikan ke semula (tanpa cascade) jika rollback
        $table->foreign('inspection_session_id')
              ->references('id')
              ->on('inspection_sessions');
    });
}
};
