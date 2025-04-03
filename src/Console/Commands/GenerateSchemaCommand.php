<?php

namespace Schema\Console\Commands;

use Illuminate\Console\Command;
use Schema\Models\Schema;
use Schema\Services\SchemaService;
use Illuminate\Support\Str;

class GenerateSchemaCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'schema:generate
                            {table : The name of the table}
                            {--fields= : The fields for the schema in format field:type,field:type}
                            {--model= : The name of the model}
                            {--namespace= : The namespace for the model}
                            {--timestamps : Include timestamps}
                            {--api : Generate API resources and controllers}
                            {--factory : Generate factory for model}
                            {--policy : Generate policy for model}
                            {--controller : Generate controller for model}
                            {--no-migration : Skip migration generation}
                            {--no-model : Skip model generation}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a schema with CRUD features';

    /**
     * The schema service.
     */
    protected $schemaService;

    /**
     * Create a new command instance.
     */
    public function __construct(SchemaService $schemaService)
    {
        parent::__construct();
        $this->schemaService = $schemaService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tableName = $this->argument('table');
        $modelName = $this->option('model') ?: Str::studly(Str::singular($tableName));
        $namespace = $this->option('namespace') ?: config('schema.model_namespace');
        $timestamps = $this->option('timestamps') ?: config('schema.timestamps', true);

        // Parse fields
        $fields = [];
        if ($fieldsOption = $this->option('fields')) {
            $fieldsArray = explode(',', $fieldsOption);
            foreach ($fieldsArray as $field) {
                [$name, $type] = explode(':', $field);
                $fields[] = [
                    'field' => $name,
                    'type' => $type,
                    'nullable' => false,
                    'unique' => false,
                    'default' => null,
                    'index' => false,
                ];
            }
        }

        // Create schema
        $schema = new Schema([
            'table_name' => $tableName,
            'model_name' => $modelName,
            'namespace' => $namespace,
            'has_timestamps' => $timestamps,
            'has_fillable' => config('schema.fillable', true),
            'has_guarded' => config('schema.guarded', false),
            'has_soft_deletes' => config('schema.soft_deletes', false),
            'schema_definition' => $fields,
            'factory' => $this->option('factory'),
            'policy' => $this->option('policy'),
            'controller' => $this->option('controller') || $this->option('api'),
            'api' => $this->option('api'),
        ]);

        $schema->save();

        $this->info("Schema created for table '{$tableName}'");

        // Generate migration
        if (!$this->option('no-migration')) {
            $migrationPath = $schema->generateMigration();
            $this->info("Migration created at {$migrationPath}");
        }

        // Generate model
        if (!$this->option('no-model')) {
            $modelPath = $this->schemaService->generateModel($schema);
            $this->info("Model created at {$modelPath}");
        }

        // Generate factory if requested
        if ($schema->factory) {
            $factoryPath = $this->schemaService->generateFactory($schema);
            $this->info("Factory created at {$factoryPath}");
        }

        // Generate controller if requested
        if ($schema->controller) {
            $controllerPath = $this->schemaService->generateController($schema);
            $this->info("Controller created at {$controllerPath}");
        }

        // Generate API resource if requested
        if ($schema->api) {
            $resourcePath = $this->schemaService->generateApiResource($schema);
            $this->info("API Resource created at {$resourcePath}");
        }

        // Generate policy if requested
        if ($schema->policy) {
            $policyName = $schema->model_name . 'Policy';
            $this->call('make:policy', [
                'name' => $policyName,
                '--model' => $schema->model_name,
            ]);
        }

        return Command::SUCCESS;
    }
}
