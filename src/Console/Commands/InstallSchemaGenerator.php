<?php

namespace Schema\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallSchemaGenerator extends Command
{
    protected $signature = 'schema:install';

    protected $description = 'Install the Schema Generator package';

    public function handle()
    {
        $this->info('Installing Schema Generator...');

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'schema',
        ]);

        // Run migrations
        $this->call('migrate');

        $this->info('Schema Generator installed successfully.');
        $this->info('You can now access the Schema Generator in your Filament admin panel at /' . config('schema.path', 'schema'));

        return Command::SUCCESS;
    }
}
