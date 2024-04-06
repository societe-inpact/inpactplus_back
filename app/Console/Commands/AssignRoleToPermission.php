<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignRoleToPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:role {role} {permission}';

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
        $roleName = $this->argument('role');
        $permissionName = $this->argument('permission');

        // Vérifier si le rôle et la permission existent
        $role = Role::where('name', $roleName)->first();
        $permission = Permission::where('name', $permissionName)->first();

        if (!$role) {
            $this->error("Le rôle '$roleName' n'existe pas.");
            return;
        }

        if (!$permission) {
            $this->error("La permission '$permissionName' n'existe pas.");
            return;
        }

        // Attribuer la permission au rôle
        $role->givePermissionTo($permission);

        $this->info("La permission '$permissionName' a été attribuée au rôle '$roleName' avec succès.");
    }
}
