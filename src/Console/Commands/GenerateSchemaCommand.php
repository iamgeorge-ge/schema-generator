<?php

namespace Schema\Console\Commands;

use Illuminate\Console\Command;
use Schema\Models\SchemaGenerator;
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
                            {--description= : Description of the schema}
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
        $description = $this->option('description') ?: "Schema for {$tableName} table";

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
        $schema = new SchemaGenerator([
            'table_name' => $tableName,
            'model_name' => $modelName,
            'description' => $description,
            'schema_definition' => $fields,
        ]);

        $schema->save();

        $this->info("Schema created for table '{$tableName}'");

        // Generate model
        if (!$this->option('no-model')) {
            $modelPath = $this->schemaService->generateModel($schema);
            $this->info("Model processing: {$modelPath}");
        }

        // Generate factory if requested
        $factoryPath = $this->schemaService->generateFactory($schema);
        $this->info("Factory processing: {$factoryPath}");

        // Generate controller if requested
        $controllerPath = $this->schemaService->generateController($schema);
        $this->info("Controller processing: {$controllerPath}");

        // Generate API resource if requested
        $resourcePath = $this->schemaService->generateApiResource($schema);
        $this->info("API Resource processing: {$resourcePath}");

        return Command::SUCCESS;
    }
}
