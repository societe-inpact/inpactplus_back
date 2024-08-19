<?php

namespace App\Http\Middleware\ModuleAccess;

use App\Models\Misc\User;
use App\Models\Modules\Module;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyCompanyFolderModuleAccess
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
        $user = User::with([
            'folders.modules',
            'folders.modules.companyAccess',
            'folders.modules.companyFolderAccess',
            'folders.modules.userAccess',
            'folders.modules.userPermissions',
            'folders.company',
            'folders.mappings',
            'folders.interfaces',
            'folders.employees',
            'folders',
            'company'
        ])->find(Auth::id());

        if (!$user) {
            return response()->json(['error' => 'Vous n\'êtes pas connecté'], 401);
        }

        if ($user->hasRole('inpact')) {
            return $next($request);
        }

        $companyFolderIds = $user->folders->pluck('id')->unique()->toArray();
        $companyFolderHasAccess = Module::where('name', $moduleName)
            ->whereHas('companyFolderAccess', function ($query) use ($companyFolderIds) {
                $query->where('has_access', true)
                    ->whereIn('company_folder_id', $companyFolderIds);
            })->exists();

        if (!$companyFolderHasAccess) {
            return response()->json(['error' => 'Votre dossier d\'entreprise n\'a pas accès à ce module'], 401);
        }

        return $next($request);
    }
}
