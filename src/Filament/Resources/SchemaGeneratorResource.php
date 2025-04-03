<?php

namespace Schema\Filament\Resources;

use Schema\Filament\Resources\SchemaGeneratorResource\Pages;
use Schema\Filament\Resources\SchemaGeneratorResource\RelationManagers;
use Schema\Models\SchemaGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchemaGeneratorResource extends Resource
{
    protected static ?string $model = SchemaGenerator::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Schema Generator';
    protected static ?string $modelLabel = 'Schema';
    protected static ?string $pluralModelLabel = 'Schemas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('table_name')
                    ->required()
                    ->helperText('The exact name to use for the database table')
                    ->maxLength(255),
                Forms\Components\TextInput::make('model_name')
                    ->required()
                    ->helperText('The name of the model class')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Section::make('Generation Options')
                    ->schema([
                        Forms\Components\Toggle::make('generate_migration')
                            ->label('Generate Migration')
                            ->default(true),
                        Forms\Components\Toggle::make('generate_factory')
                            ->label('Generate Factory')
                            ->default(true),
                        Forms\Components\Toggle::make('generate_seeder')
                            ->label('Generate Seeder')
                            ->default(false),
                        Forms\Components\Toggle::make('generate_controller')
                            ->label('Generate Controller')
                            ->default(true),
                        Forms\Components\Toggle::make('generate_api')
                            ->label('Generate API Resource')
                            ->default(true),
                        Forms\Components\Toggle::make('generate_filament_resource')
                            ->label('Generate Filament Resource')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('table_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchemaGenerators::route('/'),
            'create' => Pages\CreateSchemaGenerator::route('/create'),
            'edit' => Pages\EditSchemaGenerator::route('/{record}/edit'),
        ];
    }
}
