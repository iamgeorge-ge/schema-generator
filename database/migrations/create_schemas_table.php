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
        Schema::create('schemas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('generate_migration')->default(true);
            $table->boolean('generate_factory')->default(true);
            $table->boolean('generate_seeder')->default(false);
            $table->boolean('generate_controller')->default(true);
            $table->boolean('generate_api')->default(true);
            $table->boolean('generate_filament_resource')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schemas');
    }
};
