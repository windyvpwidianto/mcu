<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['L', 'P'])->nullable()->after('email');
            $table->date('date_birth')->nullable()->after('gender');
            $table->string('department_name')->nullable()->after('gender');
            $table->string('employee_id')->nullable()->after('department_name');
            $table->date('date_commenced')->nullable()->after('employee_id');
            $table->unsignedBigInteger('role_id')->nullable()->after('date_commenced');
            // kalau ada relasi ke tabel role_user_permits
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'date_birth',
                'username',
                'department_name',
                'employee_id',
                'date_commenced',
                'role_id',
            ]);
        });
    }
};
