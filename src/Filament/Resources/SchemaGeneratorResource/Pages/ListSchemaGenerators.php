<?php

namespace Schema\Filament\Resources\SchemaGeneratorResource\Pages;

use Schema\Filament\Resources\SchemaGeneratorResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

class ListSchemaGenerators extends ListRecords
{
    protected static string $resource = SchemaGeneratorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            DeleteBulkAction::make()
                ->label('Delete selected')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Schemas deleted')
                        ->body('The selected schemas and their migration files have been deleted.')
                ),
        ];
    }
}
