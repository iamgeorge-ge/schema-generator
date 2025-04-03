<?php

namespace Schema\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallSchemaGenerator extends Command
{
    protected $signature = 'schema:install';

    protected $description = 'Install the Schema Generator package';

    public function handle()
    {
        $this->info('Installing Schema Generator...');

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'schema',
        ]);

        // Copy Filament resources to app directory
        $this->publishFilamentResources();

        // Add registration method to ALL panel providers
        $this->addRegistrationToAllPanels();

        // Run migrations
        $this->call('migrate');

        $this->info('Schema Generator installed successfully.');
        $this->info('You can now access the Schema Generator in your Filament admin panel at /admin');

        return Command::SUCCESS;
    }

    protected function publishFilamentResources(): void
    {
        // Create directories if they don't exist
        $resourceDir = app_path('Filament/Resources');
        $pagesDir = app_path('Filament/Resources/SchemaGeneratorResource/Pages');

        if (!File::isDirectory($resourceDir)) {
            File::makeDirectory($resourceDir, 0755, true);
        }

        if (!File::isDirectory($pagesDir)) {
            File::makeDirectory($pagesDir, 0755, true);
        }

        // Copy resource file - exact copy without namespace changes
        $sourceResourceFile = __DIR__ . '/../../Filament/Resources/SchemaGeneratorResource.php';
        $targetResourceFile = app_path('Filament/Resources/SchemaGeneratorResource.php');

        if (!File::exists($targetResourceFile) && File::exists($sourceResourceFile)) {
            File::copy($sourceResourceFile, $targetResourceFile);
            $this->info('Published SchemaGeneratorResource to app/Filament/Resources');
        }

        // Copy page files - exact copies without namespace changes
        $sourcePagesDir = __DIR__ . '/../../Filament/Resources/SchemaGeneratorResource/Pages';

        if (File::isDirectory($sourcePagesDir)) {
            foreach (File::files($sourcePagesDir) as $file) {
                $targetFile = $pagesDir . '/' . $file->getFilename();

                if (!File::exists($targetFile)) {
                    File::copy($file->getPathname(), $targetFile);
                }
            }
            $this->info('Published SchemaGeneratorResource page files to app/Filament/Resources/SchemaGeneratorResource/Pages');
        }

        // Copy model - exact copy without namespace changes
        $sourceModelFile = __DIR__ . '/../../Models/SchemaGenerator.php';
        $targetModelFile = app_path('Models/SchemaGenerator.php');

        if (!File::exists($targetModelFile) && File::exists($sourceModelFile)) {
            // Make sure Models directory exists
            if (!File::isDirectory(app_path('Models'))) {
                File::makeDirectory(app_path('Models'), 0755, true);
            }

            File::copy($sourceModelFile, $targetModelFile);
            $this->info('Published SchemaGenerator model to app/Models');
        }
    }

    /**
     * Force add registration method to ALL panel providers in the application
     */
    protected function addRegistrationToAllPanels(): void
    {
        $providerPaths = [
            // Look for panel providers in app/Providers
            app_path('Providers'),
            // Look for panel providers in app/Providers/Filament
            app_path('Providers/Filament'),
        ];

        $phpFiles = [];

        // Collect all PHP files in provider directories
        foreach ($providerPaths as $path) {
            if (File::isDirectory($path)) {
                $phpFiles = array_merge($phpFiles, File::files($path));
            }
        }

        foreach ($phpFiles as $file) {
            if (!Str::endsWith($file->getFilename(), '.php')) {
                continue;
            }

            $content = File::get($file->getPathname());

            // Does this look like a panel provider?
            if (Str::contains($content, 'PanelProvider') && Str::contains($content, 'panel(') && Str::contains($content, '->login()')) {
                // Check if registration is already there
                if (Str::contains($content, '->registration()')) {
                    continue;
                }

                // Add registration after login
                $newContent = preg_replace(
                    '/->login\(\)(\s*?)->/m',
                    "->login()\$1->registration()\$1->",
                    $content
                );

                if ($newContent !== $content) {
                    File::put($file->getPathname(), $newContent);
                    $this->info('Added registration to panel provider: ' . $file->getFilename());
                }
            }
        }
    }
}
