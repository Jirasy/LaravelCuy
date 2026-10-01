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
        Schema::create('Assets', function (Blueprint $table) {
            $table->id();
            $table->string('ast_name');
            $table->string('ast_code')->unique();
            $table->integer('ast_qty');
            $table->string('ast_brand');
            $table->string('ast_category');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Assets');
    }
};
