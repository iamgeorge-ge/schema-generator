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

            Actions\Action::make('generate_migration')
                ->label('Generate Migration')
                ->icon('heroicon-o-code-bracket')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        $migrationPath = $this->record->generateMigration();
                        Notification::make()
                            ->title('Migration generated successfully!')
                            ->body("Migration file created at {$migrationPath}")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error generating migration')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
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
