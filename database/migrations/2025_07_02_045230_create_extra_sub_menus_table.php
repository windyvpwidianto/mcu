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
        Schema::create('extra_sub_menus', function (Blueprint $table) {
            $table->id();
            $table->string('menu')->nullable();
            $table->string('icon')->nullable();
            $table->string('route')->nullable();
            $table->string('status')->nullable();
            $table->integer('urutan')->nullable();
            $table->unsignedBigInteger('sub_menu_id')->nullable();
            $table->foreign('sub_menu_id')->references('id')->on('sub_menus')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_sub_menus');
    }
};
