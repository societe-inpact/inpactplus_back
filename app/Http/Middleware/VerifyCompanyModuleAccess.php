<?php

namespace App\Http\Middleware;

use App\Models\Companies\CompanyFolder;
use App\Models\Employees\EmployeeFolder;
use App\Models\Modules\Module;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyCompanyModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param string $moduleName
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $moduleName): Response
    {
        $user = Auth::user()->load('roles', 'companies', 'folders');
        if (!$user) {
            return response()->json(['error' => 'Vous n\'êtes pas connecté'], 401);
        }

        // Si l'utilisateur est un utilisateur inpact, on passe
        if ($user->hasRole('inpact')) {
            return $next($request);
        }

        $companyIds = $user->folders->pluck('company_id')->toArray();
        $folderIds = $user->folders->pluck('id')->toArray();

        // Vérification de l'accès au dossier pour l'utilisateur
        $userFolderHasAccess = EmployeeFolder::where('user_id', $user->id)
            ->whereIn('company_folder_id', $folderIds)
            ->where('has_access', true)
            ->exists();

        if (!$userFolderHasAccess) {
            return response()->json(['error' => 'Vous n\'avez pas accès à ce dossier'], 401);
        }

        // Vérification de l'accès au module via l'entreprise
        $companyHasAccess = Module::where('name', $moduleName)
            ->whereHas('companyModuleAccess', function ($query) use ($companyIds) {
                $query->where('has_access', true)
                    ->whereIn('company_id', $companyIds);
            })->exists();

        if (!$companyHasAccess) {
            return response()->json(['error' => 'Votre entreprise n\'a pas accès à ce module'], 401);
        }

        // Vérification de l'accès au module via le dossier de l'entreprise
        $userHasAccess = Module::where('name', $moduleName)
            ->whereHas('companyFolderModuleAccess', function ($query) use ($folderIds) {
                $query->where('has_access', true)
                    ->whereIn('company_folder_id', $folderIds);
            })->exists();

        if (!$userHasAccess) {
            return response()->json(['error' => 'Votre dossier d\'entreprise n\'a pas accès à ce module'], 401);
        }

        return $next($request);
    }


}
