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
        Schema::create('healths', function (Blueprint $table) {
            $table->id();
            $table->string('hlt_name', 191);
            $table->string('hlt_blood_type', 5);
            $table->string('hlt_illness', 191)->nullable();;
            $table->string('hlt_disability', 191)->nullable();;
            $table->string('hlt_height', 11);
            $table->string('hlt_weight', 11);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('healths');
    }
};
