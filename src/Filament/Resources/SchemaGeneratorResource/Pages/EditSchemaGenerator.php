<?php

namespace Schema\Filament\Resources\SchemaGeneratorResource\Pages;

use Schema\Filament\Resources\SchemaGeneratorResource;
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
                ->label('Generate Code')
                ->color('success')
                ->icon('heroicon-o-code-bracket')
                ->action(fn($record) => $record->generateCode()),

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
