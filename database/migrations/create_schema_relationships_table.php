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
        Schema::create('schema_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schema_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_schema_id')->constrained('schemas')->cascadeOnDelete();
            $table->string('type'); // belongsTo, hasMany, hasOne, belongsToMany
            $table->string('name');
            $table->string('foreign_key')->nullable();
            $table->string('local_key')->nullable();
            $table->string('pivot_table')->nullable();
            $table->string('pivot_foreign_key')->nullable();
            $table->string('pivot_related_key')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schema_relationships');
    }
};
