<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignRoleToUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:user-role {user} {role}';

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
        $userId = $this->argument('user');
        $roleName = $this->argument('role');

        // Vérifier si l'utilisateur et le rôle existent
        $user = User::where('id', $userId)->first();
        $role = Role::where('name', $roleName)->first();

        if (!$user) {
            $this->error("L'utilisateur '$userId' n'existe pas.");
            return;
        }

        if (!$role) {
            $this->error("Le rôle '$roleName' n'existe pas.");
            return;
        }

        // Attribuer le rôle à l'utilisateur
        $user->assignRole($role);

        $this->info("Le rôle '$roleName' a été attribué à l'utilisateur '$userId' avec succès.");
    }
}
