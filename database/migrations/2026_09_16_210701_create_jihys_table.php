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
        Schema::create('jihys', function (Blueprint $table) {
            $table->id();
            $table->string('jihy_model_name');
            $table->string('jihy_model_type');
            $table->string('jihy_fuel_type');
            $table->string('jihy_transmission');
            $table->string('jihy_body_type');
            $table->string('jihy_seating_capacity');
            $table->string('jihy_engine_capacity');
            $table->string('jihy_price');
            $table->boolean('jihy_status')->default(true);
            $table->string('jihy_origin');
            $table->integer('jihy_founded_year')->nullable();
            $table->string('jihy_founder')->nullable();
            $table->string('jihy_headquarters')->nullable();
            $table->text('jihy_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jihys');
    }
};
