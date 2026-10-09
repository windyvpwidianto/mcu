<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->where('username', '')->update(['username' => null]);
        DB::table('users')->where('email', '')->update(['email' => null]);
        DB::table('users')->where('employee_id', '')->update(['employee_id' => null]);
        DB::table('users')->where('nik', '')->update(['nik' => null]);
        DB::table('users')->where('phone_number', '')->update(['phone_number' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed for null normalization
    }
};
