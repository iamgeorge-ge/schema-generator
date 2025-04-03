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
        Schema::table('schemas', function (Blueprint $table) {
            if (!Schema::hasColumn('schemas', 'migration_path')) {
                $table->string('migration_path')->nullable();
            }
            if (!Schema::hasColumn('schemas', 'model_path')) {
                $table->string('model_path')->nullable();
            }
            if (!Schema::hasColumn('schemas', 'collection_name')) {
                $table->string('collection_name')->nullable();
            }
            if (!Schema::hasColumn('schemas', 'namespace')) {
                $table->string('namespace')->nullable();
            }
            if (!Schema::hasColumn('schemas', 'model_options')) {
                $table->json('model_options')->nullable();
            }
            if (!Schema::hasColumn('schemas', 'model_code')) {
                $table->text('model_code')->nullable();
            }
            if (!Schema::hasColumn('schemas', 'factory')) {
                $table->boolean('factory')->default(false);
            }
            if (!Schema::hasColumn('schemas', 'policy')) {
                $table->boolean('policy')->default(false);
            }
            if (!Schema::hasColumn('schemas', 'seeder')) {
                $table->boolean('seeder')->default(false);
            }
            if (!Schema::hasColumn('schemas', 'controller')) {
                $table->boolean('controller')->default(false);
            }
            if (!Schema::hasColumn('schemas', 'has_timestamps')) {
                $table->boolean('has_timestamps')->default(true);
            }
            if (!Schema::hasColumn('schemas', 'has_fillable')) {
                $table->boolean('has_fillable')->default(true);
            }
            if (!Schema::hasColumn('schemas', 'has_guarded')) {
                $table->boolean('has_guarded')->default(false);
            }
            if (!Schema::hasColumn('schemas', 'has_soft_deletes')) {
                $table->boolean('has_soft_deletes')->default(false);
            }
            if (!Schema::hasColumn('schemas', 'fillable_fields')) {
                $table->json('fillable_fields')->nullable();
            }
            if (!Schema::hasColumn('schemas', 'field_selection')) {
                $table->json('field_selection')->nullable();
            }
            if (!Schema::hasColumn('schemas', 'model_relationships')) {
                $table->json('model_relationships')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to drop columns
    }
};
