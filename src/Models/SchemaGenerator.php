<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SchemaGenerator extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_name',
        'schema_definition',
        'migration_path',
        'model_name',
        'model_path',
        'collection_name',
        'namespace',
        'model_options',
        'model_code',
        'factory',
        'policy',
        'seeder',
        'controller',
        'has_timestamps',
        'has_fillable',
        'has_guarded',
        'has_soft_deletes',
        'fillable_fields',
        'field_selection',
        'model_relationships',
    ];

    protected $casts = [
        'schema_definition' => 'array',
        'model_options' => 'array',
        'fillable_fields' => 'array',
        'field_selection' => 'array',
        'model_relationships' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['suggested_model_name'];

    /**
     * Get suggested model name from table name
     */
    public function getSuggestedModelNameAttribute()
    {
        // Don't force lowercase, respect the original case
        return Str::studly(Str::singular($this->table_name ?? ''));
    }

    /**
     * Get model name with default from table name if not set
     */
    public function getModelNameAttribute($value)
    {
        return $value ?: $this->suggested_model_name;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Set default model name when creating a new schema
        static::creating(function ($schema) {
            if (empty($schema->model_name) && !empty($schema->table_name)) {
                $schema->model_name = Str::studly(Str::singular($schema->table_name));
            }
        });

        // Delete the migration file and model file when the schema is deleted
        static::deleting(function ($schema) {
            // Delete migration file
            if (!empty($schema->migration_path) && File::exists($schema->migration_path)) {
                File::delete($schema->migration_path);
            }

            // Delete model file
            if (!empty($schema->model_name) && !empty($schema->namespace)) {
                $modelPath = base_path(str_replace('\\', '/', $schema->namespace)) . '/' . $schema->model_name . '.php';
                if (File::exists($modelPath)) {
                    File::delete($modelPath);
                }
            }
        });
    }

    /**
     * Generate a migration file based on the schema definition
     *
     * @return string The path to the generated migration file
     */
    public function generateMigration(): string
    {
        // Use table name exactly as entered but convert to lowercase
        $tableName = strtolower($this->table_name);

        // For migration file name only, use plural form but preserve case
        $migrationTableName = Str::plural(Str::snake($tableName));
        $migrationName = "create_{$migrationTableName}_table";

        // If we already have a migration path and the file exists, update it instead of creating a new one
        if (!empty($this->migration_path) && File::exists($this->migration_path)) {
            // Read the migration file content
            $content = File::get($this->migration_path);

            // Build the schema columns
            $schemaCode = $this->buildSchemaCode();

            // Update the existing migration file
            $content = preg_replace(
                '/Schema::create\(\'([^\']*)\', function \(Blueprint \$table\) {(.*?)}\);/s',
                "Schema::create('" . strtolower(Str::plural($tableName)) . "', function (Blueprint \$table) {\n            \$table->id();\n$schemaCode            \$table->timestamps();\n        });",
                $content
            );

            // Write the updated content back to the file
            File::put($this->migration_path, $content);

            return $this->migration_path;
        }

        // Create a new migration file via Artisan
        Artisan::call('make:migration', [
            'name' => $migrationName,
        ]);

        // Find the latest migration file manually
        $migrationsPath = database_path('migrations');
        $files = File::files($migrationsPath);

        // Sort by modification time (newest first)
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Find the migration with our name
        $migrationFile = null;
        foreach ($files as $file) {
            if (Str::contains($file->getFilename(), Str::snake($migrationName))) {
                $migrationFile = $file;
                break;
            }
        }

        if (!$migrationFile) {
            throw new \Exception("Failed to create migration file.");
        }

        $migrationPath = $migrationFile->getPathname();

        // Read the migration file content
        $content = File::get($migrationPath);

        // Build the schema columns
        $schemaCode = $this->buildSchemaCode();

        // Replace the schema placeholder with our columns
        $content = preg_replace(
            '/Schema::create\(\'([^\']*)\', function \(Blueprint \$table\) {(.*?)}\);/s',
            "Schema::create('" . strtolower(Str::plural($tableName)) . "', function (Blueprint \$table) {\n            \$table->id();\n$schemaCode            \$table->timestamps();\n        });",
            $content
        );

        // Write the updated content back to the file
        File::put($migrationPath, $content);

        // Save the migration path to the model
        $this->update(['migration_path' => $migrationPath]);

        return $migrationPath;
    }

    /**
     * Build the schema code from the schema definition
     *
     * @return string
     */
    protected function buildSchemaCode(): string
    {
        $code = '';

        foreach ($this->schema_definition as $item) {
            if (!isset($item['field']) || !isset($item['type'])) {
                continue;
            }

            $field = $item['field'];
            $type = $item['type'];

            // Handle column types that may have length parameters
            if (!empty($item['length']) && in_array($type, ['string', 'char', 'decimal', 'float', 'double'])) {
                // For decimal/float, check if the length contains a comma for precision and scale
                if (in_array($type, ['decimal', 'float', 'double']) && strpos($item['length'], ',') !== false) {
                    [$precision, $scale] = explode(',', $item['length']);
                    $fieldCode = "\$table->{$type}('{$field}', {$precision}, {$scale})";
                } else {
                    $fieldCode = "\$table->{$type}('{$field}', {$item['length']})";
                }
            } else {
                $fieldCode = "\$table->{$type}('{$field}')";
            }

            // Add modifiers
            if (!empty($item['unsigned']) && $item['unsigned']) {
                $fieldCode .= "->unsigned()";
            }

            if (!empty($item['nullable']) && $item['nullable']) {
                $fieldCode .= "->nullable()";
            }

            if (!empty($item['autoIncrement']) && $item['autoIncrement']) {
                $fieldCode .= "->autoIncrement()";
            }

            if (!empty($item['unique']) && $item['unique']) {
                $fieldCode .= "->unique()";
            }

            if (!empty($item['index']) && $item['index']) {
                $fieldCode .= "->index()";
            }

            // Handle foreign key constraint
            if (!empty($item['constraint']) && $item['constraint']) {
                if ($type === 'foreignId') {
                    $relationTable = Str::singular(preg_replace('/_id$/', '', $field));

                    // For foreignIds, we can use the simpler constrained syntax if it follows conventions
                    if ($relationTable !== $field) {
                        $constrainedCode = "->constrained()";

                        // Add cascade options
                        if (!empty($item['cascade']) && $item['cascade']) {
                            $constrainedCode .= "->onDelete('cascade')";
                        }

                        if (!empty($item['updateCascade']) && $item['updateCascade']) {
                            $constrainedCode .= "->onUpdate('cascade')";
                        }

                        $fieldCode .= $constrainedCode;
                    }
                } else {
                    // For other column types, add a separate foreign key declaration
                    $constraintCode = "\$table->foreign('{$field}')";

                    // Try to guess the related table
                    $relationTable = Str::plural(preg_replace('/_id$/', '', $field));
                    $constraintCode .= "->references('id')->on('{$relationTable}')";

                    if (!empty($item['cascade']) && $item['cascade']) {
                        $constraintCode .= "->onDelete('cascade')";
                    }

                    if (!empty($item['updateCascade']) && $item['updateCascade']) {
                        $constraintCode .= "->onUpdate('cascade')";
                    }

                    $fieldCode .= ";\n            {$constraintCode}";
                }
            } else if (!empty($item['cascade']) && $item['cascade'] && $type === 'foreignId') {
                // Handle cascade without constraint for foreignId (which has implicit constraint)
                $fieldCode .= "->constrained()->onDelete('cascade')";

                if (!empty($item['updateCascade']) && $item['updateCascade']) {
                    $fieldCode .= "->onUpdate('cascade')";
                }
            } else if (!empty($item['updateCascade']) && $item['updateCascade'] && $type === 'foreignId') {
                // Handle update cascade without delete cascade
                $fieldCode .= "->constrained()->onUpdate('cascade')";
            }

            // Add default value if provided
            if (isset($item['default']) && $item['default'] !== '') {
                // Handle different types of default values
                if (in_array($type, ['boolean'])) {
                    // Boolean value handling
                    $defaultValue = filter_var($item['default'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                    $fieldCode .= "->default({$defaultValue})";
                } elseif (in_array($type, ['integer', 'bigInteger', 'smallInteger', 'tinyInteger', 'mediumInteger', 'float', 'double', 'decimal'])) {
                    // Numeric value handling
                    $fieldCode .= "->default({$item['default']})";
                } else {
                    // String value handling
                    $fieldCode .= "->default('{$item['default']}')";
                }
            }

            $code .= "            {$fieldCode};\n";
        }

        return $code;
    }

    /**
     * Generate a model file based on the schema definition
     *
     * @return string The generated model code
     */
    public function generateModel(): string
    {
        // Use table name with lowercase conversion
        $tableName = strtolower($this->table_name);
        $modelName = !empty($this->model_name) ? $this->model_name : Str::studly(Str::singular($tableName));
        $modelPath = !empty($this->model_path) ? $this->model_path : 'App\\Models';
        $namespace = !empty($this->namespace) ? $this->namespace : 'App\\Models';
        $collectionName = !empty($this->collection_name) ? $this->collection_name : $tableName;

        // Ensure options is always an array
        $options = [];

        // Add any direct option fields to the model_options array
        if (!empty($this->factory)) $options['factory'] = true;
        if (!empty($this->policy)) $options['policy'] = true;
        if (!empty($this->seeder)) $options['seeder'] = true;
        if (!empty($this->controller)) $options['controller'] = true;

        // Remove any references to the old relationship toggles
        if (isset($options['relationships'])) unset($options['relationships']);
        if (isset($options['has_many'])) unset($options['has_many']);
        if (isset($options['belongs_to_many'])) unset($options['belongs_to_many']);

        // Add checkbox options
        $hasTimestamps = !empty($this->has_timestamps);
        $hasFillable = !empty($this->has_fillable);
        $hasGuarded = !empty($this->has_guarded);
        $hasSoftDeletes = !empty($this->has_soft_deletes);

        // Merge with existing model_options
        if (!empty($this->model_options) && is_array($this->model_options)) {
            $options = array_merge($this->model_options, $options);
        }

        // Start building the model
        $modelCode = "<?php\n\nnamespace {$namespace};\n\n";

        // Import statements
        $modelCode .= "use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\n";
        $modelCode .= "use Illuminate\\Database\\Eloquent\\Model;\n";

        // Add imports for options
        if ($hasSoftDeletes) {
            $modelCode .= "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n";
        }

        $modelCode .= "\nclass {$modelName} extends Model\n{\n";

        // Include traits
        $traits = ["use HasFactory"];
        if ($hasSoftDeletes) {
            $traits[] = "SoftDeletes";
        }

        if (count($traits) > 0) {
            $modelCode .= "    " . implode(", ", $traits) . ";\n\n";
        }

        // Always include the table name property to avoid Laravel's automatic pluralization
        $modelCode .= "    protected \$table = '" . strtolower(Str::plural($tableName)) . "';\n\n";

        // Custom collection name if it differs from default
        if ($collectionName !== Str::plural($tableName)) {
            $modelCode .= "    protected \$collection = '{$collectionName}';\n\n";
        }

        // Timestamps setting
        if (!$hasTimestamps) {
            $modelCode .= "    public \$timestamps = false;\n\n";
        }

        // Fillable fields
        if ($hasFillable && !empty($this->schema_definition)) {
            $fillable = [];

            // If specific fillable fields are selected
            if (isset($this->field_selection)) {
                // If field_selection exists but is empty, keep fillable empty
                if (is_array($this->field_selection) && !empty($this->field_selection)) {
                    foreach ($this->field_selection as $field) {
                        $fillable[] = "'" . $field . "'";
                    }
                }
            } else {
                // Only use all fields if field_selection is not set at all
                foreach ($this->schema_definition as $field) {
                    if (isset($field['field']) && $field['field'] !== 'id') {
                        $fillable[] = "'" . $field['field'] . "'";
                    }
                }
            }

            // Always add the fillable array even if empty
            $modelCode .= "    protected \$fillable = [\n        " . implode(",\n        ", $fillable) . "\n    ];\n\n";
        }

        // Guarded fields
        if ($hasGuarded) {
            // If field_selection are specified, use them to determine guarded fields
            if (isset($this->field_selection)) {
                $selectedFields = is_array($this->field_selection) ? $this->field_selection : [];

                if (empty($selectedFields)) {
                    // If no fields are selected, don't guard anything
                    $modelCode .= "    protected \$guarded = [];\n\n";
                } else {
                    // Generate guarded array - directly use the selected fields as guarded
                    $guarded = [];

                    foreach ($this->field_selection as $fieldName) {
                        $guarded[] = "'" . $fieldName . "'";
                    }

                    // Sort alphabetically for consistency
                    sort($guarded);

                    $modelCode .= "    protected \$guarded = [\n        " . implode(",\n        ", $guarded) . "\n    ];\n\n";
                }
            } else {
                // Default guarded behavior if field_selection is not set - guard nothing
                $modelCode .= "    protected \$guarded = [];\n\n";
            }
        }

        // Casts
        if (!empty($options) && in_array('casts', $options) && !empty($this->schema_definition)) {
            $casts = [];
            foreach ($this->schema_definition as $field) {
                if (!isset($field['field']) || !isset($field['type'])) {
                    continue;
                }

                $phpType = $this->mapDatabaseTypeToPhpType($field['type']);
                if ($phpType) {
                    $casts[] = "'" . $field['field'] . "' => '" . $phpType . "'";
                }
            }

            if (count($casts) > 0) {
                $modelCode .= "    protected \$casts = [\n        " . implode(",\n        ", $casts) . "\n    ];\n\n";
            }
        }

        // Relationships
        if (!empty($this->model_relationships) && is_array($this->model_relationships)) {
            $modelCode .= $this->generateRelationshipMethods();
        }

        // Close the class
        $modelCode .= "}\n";

        // Save the generated model code
        $this->update([
            'model_code' => $modelCode,
            'model_name' => $modelName,
            'model_path' => $modelPath,
            'namespace' => $namespace,
            'collection_name' => $collectionName
        ]);

        // If factory is requested, create a factory file
        if (!empty($options) && in_array('factory', $options)) {
            $this->generateFactory($modelName);
        }

        // Create the actual model file if path is set
        if (!empty($modelPath)) {
            $this->saveModelFile($modelCode, $modelName, $modelPath);
        }

        return $modelCode;
    }

    /**
     * Save the model to a file
     *
     * @param string $modelCode
     * @param string $modelName
     * @param string $modelPath
     * @return void
     */
    protected function saveModelFile(string $modelCode, string $modelName, string $modelPath): void
    {
        // Convert namespace format to directory path
        $path = base_path(str_replace('\\', '/', $modelPath));

        // Create directory if it doesn't exist
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // Save the file
        $filePath = $path . '/' . $modelName . '.php';
        File::put($filePath, $modelCode);
    }

    /**
     * Generate a factory for the model
     *
     * @param string $modelName
     * @return void
     */
    protected function generateFactory(string $modelName): void
    {
        // Factory generation logic could be implemented here
        // This would create a database/factories/{ModelName}Factory.php file
    }

    /**
     * Map database column types to PHP types for casting
     *
     * @param string $databaseType
     * @return string|null
     */
    protected function mapDatabaseTypeToPhpType(string $databaseType): ?string
    {
        $mapping = [
            'integer' => 'integer',
            'bigInteger' => 'integer',
            'smallInteger' => 'integer',
            'tinyInteger' => 'integer',
            'mediumInteger' => 'integer',
            'unsignedInteger' => 'integer',
            'unsignedBigInteger' => 'integer',
            'unsignedSmallInteger' => 'integer',
            'unsignedTinyInteger' => 'integer',
            'unsignedMediumInteger' => 'integer',
            'float' => 'float',
            'double' => 'float',
            'decimal' => 'decimal',
            'boolean' => 'boolean',
            'date' => 'date',
            'dateTime' => 'datetime',
            'dateTimeTz' => 'datetime',
            'time' => 'string',
            'timeTz' => 'string',
            'timestamp' => 'timestamp',
            'timestampTz' => 'timestamp',
            'year' => 'integer',
            'binary' => 'string',
            'uuid' => 'string',
            'ipAddress' => 'string',
            'macAddress' => 'string',
            'json' => 'array',
            'jsonb' => 'array',
            'text' => 'string',
            'mediumText' => 'string',
            'longText' => 'string',
            'string' => 'string',
            'char' => 'string',
            'enum' => 'string',
        ];

        return $mapping[$databaseType] ?? null;
    }

    /**
     * Set the fillable fields attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setFillableFieldsAttribute($value)
    {
        $this->attributes['fillable_fields'] = is_null($value) ? null : (is_array($value) ? json_encode($value) : $value);
    }

    /**
     * Set the model name attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setModelNameAttribute($value)
    {
        if ($value === '?' || empty($value)) {
            $this->attributes['model_name'] = $this->suggested_model_name;
        } else {
            $this->attributes['model_name'] = $value;
        }
    }

    /**
     * Set the collection name attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setCollectionNameAttribute($value)
    {
        if ($value === '?' || empty($value)) {
            // Always use lowercase
            $this->attributes['collection_name'] = Str::plural(strtolower($this->table_name));
        } else {
            $this->attributes['collection_name'] = $value;
        }
    }

    /**
     * Set the field selection attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setFieldSelectionAttribute($value)
    {
        $this->attributes['field_selection'] = is_null($value) ? null : (is_array($value) ? json_encode($value) : $value);
    }

    /**
     * Set the model relationships attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setModelRelationshipsAttribute($value)
    {
        $this->attributes['model_relationships'] = is_null($value) ? null : (is_array($value) ? json_encode($value) : $value);
    }

    /**
     * Generate relationship methods for the model code
     *
     * @return string
     */
    protected function generateRelationshipMethods(): string
    {
        $modelCode = '';

        // Only handle manually defined relationships
        if (!empty($this->model_relationships) && is_array($this->model_relationships)) {
            foreach ($this->model_relationships as $relationship) {
                if (empty($relationship['type']) || empty($relationship['method_name']) || empty($relationship['related_model'])) {
                    continue;
                }

                $methodName = $relationship['method_name'];
                $relatedModel = $relationship['related_model'];
                $type = $relationship['type'];
                $params = [];

                // Add the related model as the first parameter
                $params[] = "{$relatedModel}::class";

                // Add foreign key if provided
                if (!empty($relationship['foreign_key'])) {
                    $params[] = "'{$relationship['foreign_key']}'";
                }

                // Add local key if provided
                if (!empty($relationship['local_key'])) {
                    $params[] = "'{$relationship['local_key']}'";
                }

                // Parse additional parameters if provided
                if (!empty($relationship['parameters'])) {
                    $paramLines = explode("\n", $relationship['parameters']);
                    foreach ($paramLines as $line) {
                        $line = trim($line);
                        if (!empty($line)) {
                            $params[] = $line;
                        }
                    }
                }

                $modelCode .= "    /**\n";
                $modelCode .= "     * Get the " . Str::snake($methodName, ' ') . " for the " . Str::camel($this->model_name) . "\n";
                $modelCode .= "     */\n";
                $modelCode .= "    public function {$methodName}()\n";
                $modelCode .= "    {\n";
                $modelCode .= "        return \$this->{$type}(" . implode(", ", $params) . ");\n";
                $modelCode .= "    }\n\n";
            }
        }

        return $modelCode;
    }
}
