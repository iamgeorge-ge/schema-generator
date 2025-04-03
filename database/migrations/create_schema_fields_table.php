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
        Schema::create('schema_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schema_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->boolean('nullable')->default(false);
            $table->boolean('unique')->default(false);
            $table->string('default')->nullable();
            $table->text('comment')->nullable();
            $table->integer('length')->nullable();
            $table->integer('precision')->nullable();
            $table->integer('scale')->nullable();
            $table->boolean('index')->default(false);
            $table->boolean('unsigned')->default(false);
            $table->json('attributes')->nullable();
            $table->json('validation_rules')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schema_fields');
    }
};
