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
        $user = Auth::user()->load('roles', 'companies');

        if (!$user) {
            return response()->json(['error' => 'Vous n\'êtes pas connecté'], 401);
        }

        if ($user->hasRole('inpact')){
            return $next($request);
        }

        $hasAccess = Module::whereHas('companyModuleAccess', function ($query) use ($user) {
            $query->whereIn('company_id', $user->companies->pluck('id')->toArray())
                ->where('has_access', true);
        })->where('name', $moduleName)->exists();


        if (!$hasAccess) {
            return response()->json(['error' => 'Votre entreprise n\'a pas accès à ce module'], 401);
        }

        return $next($request);
    }
}
