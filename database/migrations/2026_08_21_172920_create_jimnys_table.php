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
        Schema::create('jimnys', function (Blueprint $table) {
            $table->id();
            $table->string('jimny_nip');
            $table->string('jimny_name');
            $table->string('jimny_hobby');
            $table->string('jimny_do');
            $table->string('jimny_class');
            $table->string('jimny_project');
            $table->string('jimny_number');
            $table->string('jimny_call');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jimnys');
    }
};
