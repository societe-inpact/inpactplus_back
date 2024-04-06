<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MigrateUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:update {--path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->option('path') ?: database_path('migrations/update');

        $migrations = array_diff(
            File::glob($path.'/*.php'),
            DB::table('migrations')->pluck('migration')->toArray()
        );

        foreach ($migrations as $migration) {
            require_once $migration;
            $migrationName = pathinfo($migration, PATHINFO_FILENAME);
            (new $migrationName)->up();
            $this->line("<info>Migration: </info> $migrationName");
        }

        $this->info('Migration réussie');
    }
}
