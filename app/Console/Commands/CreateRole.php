<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ajoute un nouveau rôle à la base de données';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        Role::create(['name' => $name]);
        $this->info("Le rôle '$name' a bien été créé.");
    }
}
