<?php

namespace App\Filament\Resources\SchemaGeneratorResource\Pages;

use App\Filament\Resources\SchemaGeneratorResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSchemaGenerator extends EditRecord
{
    protected static string $resource = SchemaGeneratorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Schema deleted')
                        ->body('The schema and its migration file have been deleted.')
                ),

            Actions\Action::make('generate')
                ->label('Generate Files')
                ->color('success')
                ->icon('heroicon-o-code-bracket')
                ->action(function () {
                    // Get the record we're editing
                    $record = $this->getRecord();

                    try {
                        // Generate migration
                        if ($record->generate_migration) {
                            $migrationPath = $record->generateMigration();
                            $this->notify('success', 'Migration generated: ' . basename($migrationPath));
                        }

                        // Generate model code if necessary
                        if (method_exists($record, 'generateModel') && $record->model_name) {
                            $modelPath = $record->generateModel();
                            $this->notify('success', 'Model generated: ' . basename($modelPath));
                        }

                        // Generate controller if necessary
                        if (method_exists($record, 'generateController') && $record->generate_controller) {
                            $controllerPath = $record->generateController();
                            $this->notify('success', 'Controller generated: ' . basename($controllerPath));
                        }

                        // Generate API resource if necessary
                        if (method_exists($record, 'generateApiResource') && $record->generate_api) {
                            $apiResourcePath = $record->generateApiResource();
                            $this->notify('success', 'API Resource generated: ' . basename($apiResourcePath));
                        }
                    } catch (\Exception $e) {
                        $this->notify('danger', 'Error generating files: ' . $e->getMessage());
                    }
                }),

            Actions\Action::make('generate_model')
                ->label('Generate Model')
                ->icon('heroicon-o-code-bracket')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        $modelCode = $this->record->generateModel();
                        Notification::make()
                            ->title('Model generated successfully!')
                            ->body("Model code has been generated")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error generating model')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Schema updated')
            ->body('The schema has been updated successfully.');
    }
}
