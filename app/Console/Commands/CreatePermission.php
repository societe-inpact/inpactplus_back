<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreatePermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ajoute une nouvelle permission en base de données';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        Permission::create(['name' => $name]);
        $this->info("La permission '$name' a bien été créée.");
    }
}
