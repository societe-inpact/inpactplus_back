<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignAllPermissionsToRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:assign-all-permissions {role : Le nom du rôle}';

    protected $description = 'Assign all permissions to a role';

    public function handle()
    {
        $roleName = $this->argument('role');

        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            $this->error("Le rôle '$roleName' n'existe pas.");
            return;
        }

        $permissions = Permission::all();

        $role->syncPermissions($permissions);

        $this->info("Toutes les permissions ont été attribuées au rôle '$roleName'.");
    }
}
