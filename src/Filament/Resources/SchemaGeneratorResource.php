<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchemaGeneratorResource\Pages;
use App\Filament\Resources\SchemaGeneratorResource\RelationManagers;
use App\Models\SchemaGenerator;
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
    protected static ?string $navigationLabel = 'Schema';
    protected static ?string $modelLabel = 'Schema';
    protected static ?string $pluralModelLabel = 'Schemas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Schema Generator')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Tables')
                            ->schema([
                                Forms\Components\TextInput::make('table_name')
                                    ->required()
                                    ->helperText('The exact name to use for the database table (will not be automatically pluralized)')
                                    ->maxLength(255),
                                Forms\Components\Repeater::make('schema_definition')
                                    ->schema([
                                        // Main fields row
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\TextInput::make('field')
                                                    ->required()
                                                    ->columnSpan(1),
                                                Forms\Components\Select::make('type')
                                                    ->options([
                                                        'string' => 'String',
                                                        'boolean' => 'Boolean',
                                                        'bigInteger' => 'Big Integer',
                                                        'binary' => 'Binary',
                                                        'char' => 'Char',
                                                        'date' => 'Date',
                                                        'dateTime' => 'DateTime',
                                                        'dateTimeTz' => 'DateTimeTz',
                                                        'decimal' => 'Decimal',
                                                        'enum' => 'Enum',
                                                        'float' => 'Float',
                                                        'foreignId' => 'ForeignId',
                                                        'geometry' => 'Geometry',
                                                        'integer' => 'Integer',
                                                        'ipAddress' => 'IpAddress',
                                                        'json' => 'Json',
                                                        'jsonb' => 'Jsonb',
                                                        'longText' => 'LongText',
                                                        'macAddress' => 'MacAddress',
                                                        'mediumInteger' => 'MediumInteger',
                                                        'mediumText' => 'MediumText',
                                                        'set' => 'Set',
                                                        'smallInteger' => 'SmallInteger',
                                                        'text' => 'Text',
                                                        'time' => 'Time',
                                                        'timestamp' => 'Timestamp',
                                                        'timestampTz' => 'TimestampTz',
                                                        'timeTz' => 'TimeTz',
                                                        'tinyInteger' => 'TinyInteger',
                                                        'uuid' => 'Uuid',
                                                        'year' => 'Year',
                                                    ])
                                                    ->searchable()
                                                    ->required()
                                                    ->default('string')
                                                    ->reactive()
                                                    ->columnSpan(1),
                                                Forms\Components\TextInput::make('length')
                                                    ->label('Length')
                                                    ->placeholder('255')
                                                    ->columnSpan(1),
                                                Forms\Components\TextInput::make('default')
                                                    ->label('Default')
                                                    ->placeholder('Value')
                                                    ->columnSpan(1),
                                            ]),

                                        // Checkboxes rows
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Toggle::make('unique')
                                                    ->label('Unique')
                                                    ->inline(),
                                                Forms\Components\Toggle::make('index')
                                                    ->label('Index')
                                                    ->inline(),
                                                Forms\Components\Toggle::make('unsigned')
                                                    ->label('Unsigned')
                                                    ->inline(),
                                                Forms\Components\Toggle::make('autoIncrement')
                                                    ->label('Auto Increment')
                                                    ->inline(),
                                            ]),
                                        Forms\Components\Grid::make(4)
                                            ->schema([
                                                Forms\Components\Toggle::make('nullable')
                                                    ->label('Nullable')
                                                    ->inline(),
                                                Forms\Components\Toggle::make('constraint')
                                                    ->label('FK Constraint')
                                                    ->inline()
                                                    ->reactive(),
                                                Forms\Components\Toggle::make('cascade')
                                                    ->label('On Delete Cascade')
                                                    ->inline(),
                                                Forms\Components\Toggle::make('updateCascade')
                                                    ->label('On Update Cascade')
                                                    ->inline(),
                                            ]),
                                    ])
                                    ->addActionLabel('Add Field')
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Models')
                            ->schema([
                                Forms\Components\Tabs::make('ModelSubtabs')
                                    ->tabs([
                                        // Data tab for basic model properties
                                        Forms\Components\Tabs\Tab::make('Data')
                                            ->schema([
                                                Forms\Components\Grid::make(4)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('namespace')
                                                            ->label('Namespace')
                                                            ->default('App\\Models')
                                                            ->helperText('Default: App\\Models')
                                                            ->placeholder('App\\Models')
                                                            ->columnSpan(1),
                                                        Forms\Components\TextInput::make('model_name')
                                                            ->label('Model Name')
                                                            ->helperText('Use singular form (e.g., Post, User). Table name will be plural.')
                                                            ->placeholder(fn($record) => $record ? $record->suggested_model_name : 'User')
                                                            ->default(fn($record) => $record?->suggested_model_name)
                                                            ->columnSpan(1),
                                                        Forms\Components\TextInput::make('collection_name')
                                                            ->label('Collection Name')
                                                            ->helperText('Default: Plural of table name')
                                                            ->placeholder('users')
                                                            ->columnSpan(1),
                                                    ]),
                                                Forms\Components\Grid::make(4)
                                                    ->schema([
                                                        Forms\Components\Checkbox::make('has_timestamps')
                                                            ->label('Has Timestamps')
                                                            ->inline()
                                                            ->default(true),
                                                        Forms\Components\Checkbox::make('has_fillable')
                                                            ->label('Has Fillable')
                                                            ->inline()
                                                            ->default(true)
                                                            ->reactive()
                                                            ->afterStateUpdated(fn($state, callable $set) => $state ? $set('has_guarded', false) : null),
                                                        Forms\Components\Checkbox::make('has_guarded')
                                                            ->label('Has Guarded')
                                                            ->inline()
                                                            ->reactive()
                                                            ->afterStateUpdated(fn($state, callable $set) => $state ? $set('has_fillable', false) : null),
                                                        Forms\Components\Checkbox::make('has_soft_deletes')
                                                            ->label('Has SoftDeletes')
                                                            ->inline(),
                                                    ]),
                                                Forms\Components\Select::make('field_selection')
                                                    ->label(fn($get) => $get('has_guarded') ? 'Guarded Fields' : 'Fillable Fields')
                                                    ->multiple()
                                                    ->options(function ($get, $record) {
                                                        if (!$record || !$record->schema_definition) {
                                                            return [];
                                                        }

                                                        return collect($record->schema_definition)
                                                            ->pluck('field', 'field')
                                                            ->toArray();
                                                    })
                                                    ->helperText(fn($get) => $get('has_guarded')
                                                        ? 'Select fields that should be GUARDED (not mass-assignable). Only selected fields will be guarded.'
                                                        : 'Select fields to include in the $fillable array. If none selected, an empty array will be generated.')
                                                    ->hidden(fn($get) => !$get('has_fillable') && !$get('has_guarded'))
                                                    ->columnSpanFull(),
                                            ]),

                                        // Relationship tab for model relationships
                                        Forms\Components\Tabs\Tab::make('Relationship')
                                            ->schema([
                                                Forms\Components\Repeater::make('model_relationships')
                                                    ->schema([
                                                        Forms\Components\Grid::make(2)
                                                            ->schema([
                                                                Forms\Components\Select::make('type')
                                                                    ->label('Relationship Type')
                                                                    ->options([
                                                                        'belongsTo' => 'Belongs To',
                                                                        'hasMany' => 'Has Many',
                                                                        'hasOne' => 'Has One',
                                                                        'belongsToMany' => 'Belongs To Many',
                                                                        'hasManyThrough' => 'Has Many Through',
                                                                        'morphOne' => 'Morph One',
                                                                        'morphToMany' => 'Morph To Many',
                                                                    ])
                                                                    ->required()
                                                                    ->columnSpan(1),
                                                                Forms\Components\Select::make('related_model')
                                                                    ->label('Related Model')
                                                                    ->options(function () {
                                                                        // Get all model files from App\Models
                                                                        $modelPath = app_path('Models');
                                                                        if (!is_dir($modelPath)) {
                                                                            return [];
                                                                        }

                                                                        $models = [];
                                                                        foreach (new \DirectoryIterator($modelPath) as $file) {
                                                                            if ($file->isFile() && $file->getExtension() === 'php') {
                                                                                $modelName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                                                                                $models[$modelName] = $modelName;
                                                                            }
                                                                        }

                                                                        return $models;
                                                                    })
                                                                    ->required()
                                                                    ->reactive()
                                                                    ->afterStateUpdated(function (callable $set, $state) {
                                                                        if (!empty($state)) {
                                                                            // Set method_name default to match related_model in lowercase
                                                                            $set('method_name', strtolower($state));
                                                                        }
                                                                    })
                                                                    ->columnSpan(1),
                                                            ]),
                                                        Forms\Components\Grid::make(2)
                                                            ->schema([
                                                                Forms\Components\TextInput::make('method_name')
                                                                    ->label('Method Name')
                                                                    ->required()
                                                                    ->columnSpan(1)
                                                                    ->afterStateHydrated(function ($component, $state, $record, $get) {
                                                                        // If empty, set to related_model value in lowercase
                                                                        if (empty($state) && !empty($get('related_model'))) {
                                                                            $component->state(strtolower($get('related_model')));
                                                                        }
                                                                    }),
                                                                Forms\Components\TextInput::make('foreign_key')
                                                                    ->label('Foreign Key')
                                                                    ->columnSpan(1),
                                                            ]),
                                                    ])
                                                    ->default([])
                                                    ->collapsible()
                                                    ->itemLabel(
                                                        fn(array $state): ?string =>
                                                        isset($state['type'], $state['method_name'], $state['related_model'])
                                                            ? "{$state['type']} {$state['method_name']} → {$state['related_model']}"
                                                            : null
                                                    )
                                                    ->addActionLabel('Add Relationship')
                                                    ->columnSpanFull(),
                                            ])
                                            ->visible(fn($record) => $record !== null),

                                        // Code tab for generated code preview
                                        Forms\Components\Tabs\Tab::make('Code')
                                            ->schema([
                                                Forms\Components\Textarea::make('model_code')
                                                    ->label('Generated Model Code')
                                                    ->placeholder('Model code will appear here after generation')
                                                    ->rows(20)
                                                    ->columnSpanFull()
                                                    ->disabled(),
                                            ]),

                                        // Import tab for importing existing models
                                        Forms\Components\Tabs\Tab::make('Import')
                                            ->schema([
                                                Forms\Components\FileUpload::make('model_import')
                                                    ->label('Import Existing Model')
                                                    ->helperText('Upload an existing model file to use as a template')
                                                    ->acceptedFileTypes(['application/x-php', 'text/plain', 'text/php'])
                                                    ->maxSize(1024)
                                                    ->columnSpanFull(),
                                            ]),

                                        // Settings tab for additional options
                                        Forms\Components\Tabs\Tab::make('Settings')
                                            ->schema([
                                                Forms\Components\Toggle::make('factory')
                                                    ->label('Generate Factory')
                                                    ->helperText('Generate a factory file for this model')
                                                    ->inline(),
                                                Forms\Components\Toggle::make('policy')
                                                    ->label('Generate Policy')
                                                    ->helperText('Generate a policy file for this model')
                                                    ->inline(),
                                                Forms\Components\Toggle::make('seeder')
                                                    ->label('Generate Seeder')
                                                    ->helperText('Generate a seeder file for this model')
                                                    ->inline(),
                                                Forms\Components\Toggle::make('controller')
                                                    ->label('Generate Controller')
                                                    ->helperText('Generate a controller for this model')
                                                    ->inline(),
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('table_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('schema_definition')
                    ->label('Fields')
                    ->formatStateUsing(function ($state) {
                        if (!is_array($state)) return '';

                        return collect($state)
                            ->map(function ($item) {
                                $props = [];

                                if (!empty($item['unique']) && $item['unique']) {
                                    $props[] = 'U';
                                }

                                if (!empty($item['index']) && $item['index']) {
                                    $props[] = 'I';
                                }

                                if (!empty($item['unsigned']) && $item['unsigned']) {
                                    $props[] = 'UN';
                                }

                                if (!empty($item['autoIncrement']) && $item['autoIncrement']) {
                                    $props[] = 'AI';
                                }

                                if (!empty($item['nullable']) && $item['nullable']) {
                                    $props[] = 'N';
                                }

                                if (!empty($item['constraint']) && $item['constraint']) {
                                    $props[] = 'FK';
                                }

                                if (!empty($item['cascade']) && $item['cascade']) {
                                    $props[] = 'Cascade Delete';
                                }

                                if (!empty($item['updateCascade']) && $item['updateCascade']) {
                                    $props[] = 'Cascade Update';
                                }

                                // Add length indicator if present
                                $typeDisplay = $item['type'];
                                if (!empty($item['length'])) {
                                    $typeDisplay .= "({$item['length']})";
                                }

                                // Add default value indicator if present
                                if (isset($item['default']) && $item['default'] !== '') {
                                    $props[] = "Default: {$item['default']}";
                                }

                                $properties = !empty($props) ? ' [' . implode(', ', $props) . ']' : '';

                                return "{$item['field']} ({$typeDisplay}){$properties}";
                            })
                            ->implode(', ');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Edit schema'),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Delete schema')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Schema deleted')
                            ->body('The schema, migration file, and model file have been deleted.')
                    ),
                Tables\Actions\Action::make('generate_migration')
                    ->label('Generate Migration')
                    ->icon('heroicon-o-code-bracket')
                    ->color('success')
                    ->tooltip('Generate a migration file from this schema')
                    ->requiresConfirmation()
                    ->action(function (SchemaGenerator $record): void {
                        try {
                            $migrationPath = $record->generateMigration();
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
                Tables\Actions\Action::make('generate_model')
                    ->label('Generate Model')
                    ->icon('heroicon-o-code-bracket')
                    ->color('primary')
                    ->tooltip('Generate a model from this schema')
                    ->requiresConfirmation()
                    ->action(function (SchemaGenerator $record): void {
                        try {
                            $modelCode = $record->generateModel();
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
