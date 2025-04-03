<?php

namespace Schema\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

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

        // Create migration for missing columns
        $this->fixMissingDatabaseColumns();

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
                if (
                    Str::contains($content, '->registration()') &&
                    Str::contains($content, '->maxContentWidth(\'full\')') &&
                    Str::contains($content, 'danger => Color::Rose')
                ) {
                    continue;
                }

                // Add configuration after login
                if (!Str::contains($content, '->registration()')) {
                    $content = preg_replace(
                        '/->login\(\)(\s*?)->/m',
                        "->login()\$1->registration()\$1->",
                        $content
                    );
                }

                // Add maxContentWidth if missing
                if (!Str::contains($content, '->maxContentWidth(\'full\')')) {
                    $content = preg_replace(
                        '/->registration\(\)(\s*?)->/m',
                        "->registration()\$1->maxContentWidth('full')\$1->",
                        $content
                    );
                }

                // Add colors configuration if missing
                if (!Str::contains($content, 'danger => Color::Rose')) {
                    $colorsConfig = <<<EOT
->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
EOT;

                    $content = preg_replace(
                        '/->maxContentWidth\(\'full\'\)(\s*?)->/m',
                        "->maxContentWidth('full')\$1$colorsConfig\$1->",
                        $content
                    );
                }

                if (File::put($file->getPathname(), $content)) {
                    $this->info('Updated panel provider configuration: ' . $file->getFilename());
                }
            }
        }
    }

    /**
     * Fix missing database columns
     */
    protected function fixMissingDatabaseColumns(): void
    {
        // Check if schemas table exists
        if (!Schema::hasTable('schemas')) {
            return;
        }

        // Check and add missing columns
        $missingColumns = [];
        $columns = [
            'migration_path' => 'string',
            'model_path' => 'string',
            'collection_name' => 'string',
            'namespace' => 'string',
            'model_options' => 'json',
            'model_code' => 'text',
            'factory' => 'boolean',
            'policy' => 'boolean',
            'seeder' => 'boolean',
            'controller' => 'boolean',
            'has_timestamps' => 'boolean',
            'has_fillable' => 'boolean',
            'has_guarded' => 'boolean',
            'has_soft_deletes' => 'boolean',
            'fillable_fields' => 'json',
            'field_selection' => 'json',
            'model_relationships' => 'json',
        ];

        // Create dynamic migration content
        foreach ($columns as $column => $type) {
            if (!Schema::hasColumn('schemas', $column)) {
                $missingColumns[$column] = $type;
            }
        }

        if (empty($missingColumns)) {
            $this->info('No missing columns in schemas table.');
            return;
        }

        // Create a dynamic migration to add missing columns
        $migrationPath = database_path('migrations/' . date('Y_m_d_His') . '_add_missing_columns_to_schemas.php');

        $migrationContent = <<<EOT
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
        Schema::table('schemas', function (Blueprint \$table) {

EOT;

        foreach ($missingColumns as $column => $type) {
            switch ($type) {
                case 'string':
                    $migrationContent .= "            \$table->string('$column')->nullable();\n";
                    break;
                case 'text':
                    $migrationContent .= "            \$table->text('$column')->nullable();\n";
                    break;
                case 'json':
                    $migrationContent .= "            \$table->json('$column')->nullable();\n";
                    break;
                case 'boolean':
                    $default = in_array($column, ['has_timestamps', 'has_fillable']) ? 'true' : 'false';
                    $migrationContent .= "            \$table->boolean('$column')->default($default);\n";
                    break;
            }
        }

        $migrationContent .= <<<EOT
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schemas', function (Blueprint \$table) {
EOT;

        foreach ($missingColumns as $column => $type) {
            $migrationContent .= "            \$table->dropColumn('$column');\n";
        }

        $migrationContent .= <<<EOT
        });
    }
};
EOT;

        File::put($migrationPath, $migrationContent);
        $this->info('Created migration to add missing columns to schemas table.');
    }
}
