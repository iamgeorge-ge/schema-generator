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
        if (!Schema::hasTable('schemas')) {
            Schema::create('schemas', function (Blueprint $table) {
                $table->id();
                $table->string('table_name');
                $table->string('model_name');
                $table->text('description')->nullable();
                $table->json('schema_definition')->nullable();
                $table->string('migration_path')->nullable();
                $table->string('model_path')->nullable();
                $table->string('collection_name')->nullable();
                $table->string('namespace')->nullable();
                $table->json('model_options')->nullable();
                $table->text('model_code')->nullable();
                $table->boolean('factory')->default(false);
                $table->boolean('policy')->default(false);
                $table->boolean('seeder')->default(false);
                $table->boolean('controller')->default(false);
                $table->boolean('has_timestamps')->default(true);
                $table->boolean('has_fillable')->default(true);
                $table->boolean('has_guarded')->default(false);
                $table->boolean('has_soft_deletes')->default(false);
                $table->json('fillable_fields')->nullable();
                $table->json('field_selection')->nullable();
                $table->json('model_relationships')->nullable();
                $table->boolean('generate_migration')->default(true);
                $table->boolean('generate_factory')->default(true);
                $table->boolean('generate_seeder')->default(false);
                $table->boolean('generate_controller')->default(true);
                $table->boolean('generate_api')->default(true);
                $table->boolean('generate_filament_resource')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schemas');
    }
};
