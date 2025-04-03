# Schema Generator for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/iamgeor.ge/schema-generator.svg?style=flat-square)](https://packagist.org/packages/iamgeor.ge/schema-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/iamgeor.ge/schema-generator.svg?style=flat-square)](https://packagist.org/packages/iamgeor.ge/schema-generator)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

A powerful Laravel package that provides a full-featured schema management system built on top of the Filament admin panel. It enables you to create, configure, and manage database schemas through an intuitive CRUD interface, then automatically generate corresponding migrations, models, and related files.

## Key Benefits

- **Filament-powered UI**: Leverages the beautiful and responsive Filament admin panel
- **Complete CRUD operations**: Create, read, update, and delete schema definitions through the UI
- **Code generation**: Automatically generate models, migrations, and more
- **Seamless integration**: Works with your existing Laravel and Filament setup

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Filament 3.0 or higher

## Installation

You can install the package via composer:

```bash
composer require iamgeor.ge/schema-generator
```

After installing the package, run the installation command:

```bash
php artisan schema:install
```

This will:

- Publish the configuration file
- Run migrations
- Set up the Filament panel

## Usage

After installation, you can access the schema generator through the Filament admin panel at `/schema`.

### CRUD Operations

The package provides a full Filament-powered CRUD interface for managing your database schemas:

1. **Create**: Define new schemas with table name, model name, and fields
2. **Read**: View all your defined schemas in a searchable, filterable table
3. **Update**: Modify existing schemas, add/remove fields, adjust options
4. **Delete**: Remove schemas that are no longer needed

### Schema Generation

For each schema, you can:

1. Define the table structure (fields, types, constraints)
2. Configure model options (fillable, casts, relationships)
3. Generate migrations and models with a single click
4. Preview the generated code before saving

### Integration with Your Application

The generated code is automatically placed in your application's appropriate directories:

- Migrations in `database/migrations/`
- Models in `app/Models/` (configurable)

### Screenshots

[//]: # "Add some screenshots here when available"

## Features

- **Filament Admin Panel Integration**: Built directly on Filament 3.x for a polished admin experience
- **Complete CRUD Operations**: Full interface for creating, reading, updating, and deleting schemas
- **Advanced Schema Definition**: Define columns with types, constraints, relationships, and more
- **Code Generation**:
  - Database migrations
  - Eloquent models with proper attributes
  - Support for relationships
  - Timestamps, soft deletes, and other Laravel features
- **Customization**: Configure paths, namespaces, and generation options
- **Developer Experience**: Intuitive UI with instant feedback and code previews
- **Extensibility**: Built to be extended with custom generators and features

## Configuration

The package publishes a configuration file that allows you to customize various aspects of code generation:

```bash
php artisan vendor:publish --tag="schema"
```

Configuration options:

```php
return [
    // Path where the Filament panel will be accessible
    'path' => 'schema',

    // Generation paths
    'paths' => [
        'models' => app_path('Models'),
        'migrations' => database_path('migrations'),
    ],

    // Default generation options
    'generation' => [
        'timestamps' => true,
        'soft_deletes' => false,
        'fillable' => true,
        'namespace' => 'App\\Models',
    ],
];
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [George](https://github.com/iamgeorge-ge)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
