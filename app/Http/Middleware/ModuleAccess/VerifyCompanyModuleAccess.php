<?php

namespace App\Http\Middleware\ModuleAccess;

use App\Models\Misc\User;
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
            return $this->errorResponse('Vous n\'êtes pas connecté', 401);
        }

        if ($user->hasRole('inpact')) {
            return $next($request);
        }

        $companyIds = $user->folders->pluck('company.id')->unique()->toArray();
        $companyHasAccess = Module::where('name', $moduleName)
            ->whereHas('companyAccess', function ($query) use ($companyIds) {
                $query->where('has_access', true)
                    ->whereIn('company_id', $companyIds);
            })->exists();

        if (!$companyHasAccess) {
            return $this->errorResponse('Votre entreprise n\'a pas accès à ce module', 401);
        }

        return $next($request);
    }
}
