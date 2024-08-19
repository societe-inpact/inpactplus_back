<?php

namespace App\Http\Middleware\ModuleAccess;

use App\Models\Misc\User;
use App\Models\Modules\Module;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyUserModuleAccess
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
        $userIds = $user->pluck('id')->toArray();
        $companyFolderHasAccess = Module::where('name', $moduleName)
            ->whereHas('userAccess', function ($query) use ($userIds) {
                $query->where('has_access', true)
                    ->whereIn('user_id', $userIds);
            })->exists();


        if (!$companyFolderHasAccess) {
            return response()->json(['error' => 'Vous n\'avez pas accès à ce module'], 401);
        }

        return $next($request);
    }
}
