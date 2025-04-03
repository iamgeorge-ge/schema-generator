<?php

namespace Schema\Services;

use Schema\Models\SchemaGenerator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SchemaService
{
    /**
     * Generate a model from a schema
     *
     * @param SchemaGenerator $schema
     * @return string
     */
    public function generateModel(SchemaGenerator $schema): string
    {
        return $schema->model_name;
    }

    /**
     * Generate a factory for a schema
     *
     * @param SchemaGenerator $schema
     * @return string
     */
    public function generateFactory(SchemaGenerator $schema): string
    {
        return $schema->table_name;
    }

    /**
     * Generate a controller for a schema
     *
     * @param SchemaGenerator $schema
     * @return string
     */
    public function generateController(SchemaGenerator $schema): string
    {
        return $schema->table_name . 'Controller';
    }

    /**
     * Generate an API resource for a schema
     *
     * @param SchemaGenerator $schema
     * @return string
     */
    public function generateApiResource(SchemaGenerator $schema): string
    {
        return $schema->model_name . 'Resource';
    }
}
