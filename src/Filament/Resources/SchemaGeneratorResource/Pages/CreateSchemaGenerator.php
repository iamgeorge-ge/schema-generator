<?php

namespace App\Filament\Resources\SchemaGeneratorResource\Pages;

use App\Filament\Resources\SchemaGeneratorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSchemaGenerator extends CreateRecord
{
    protected static string $resource = SchemaGeneratorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure model_relationships is initialized as an empty array
        $data['model_relationships'] = [];

        return $data;
    }
}
