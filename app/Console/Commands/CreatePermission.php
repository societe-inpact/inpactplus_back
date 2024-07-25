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
    protected $signature = 'permission:create {name} {label}';

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
        $label = $this->argument('label');
        Permission::create(['name' => $name, 'label' => $label]);
        $this->info("La permission '$label' a bien été créée.");
    }
}
