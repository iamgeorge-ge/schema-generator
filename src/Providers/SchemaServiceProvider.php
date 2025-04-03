<?php

namespace Schema\Providers;

use Illuminate\Support\ServiceProvider;
use Schema\Providers\Filament\AdminPanelProvider;
use Schema\Filament\Resources\SchemaGeneratorResource;
use Schema\Console\Commands\InstallSchemaGenerator;

class SchemaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/schema.php',
            'schema'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load package routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load package views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'schema');

        // Register Filament panels and resources
        if (class_exists('\Filament\Facades\Filament')) {
            // Register Filament panel
            $this->app->register(AdminPanelProvider::class);

            // Register Filament resources
            $this->app->resolving('filament', function () {
                \Filament\Facades\Filament::registerResources([
                    SchemaGeneratorResource::class,
                ]);
            });
        }

        // Register commands
        if ($this->app->runningInConsole()) {
            // Discover and register commands from the Console directory
            $commandsDirectory = __DIR__ . '/../Console/Commands';
            if (is_dir($commandsDirectory)) {
                foreach (glob($commandsDirectory . '/*.php') as $file) {
                    $className = pathinfo($file, PATHINFO_FILENAME);
                    $class = "\\Schema\\Console\\Commands\\{$className}";
                    if (class_exists($class)) {
                        $this->commands([$class]);
                    }
                }
            }

            // Publish resources
            $this->publishes([
                __DIR__ . '/../../config/schema.php' => config_path('schema.php'),
                __DIR__ . '/../../resources/views' => resource_path('views/vendor/schema'),
            ], 'schema');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], 'schema-migrations');

            // Register console commands
            $this->commands([
                InstallSchemaGenerator::class,
            ]);
        }
    }
}
