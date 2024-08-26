<?php

namespace App\Http\Middleware;

use App\Models\Employees\EmployeeFolder;
use App\Models\Misc\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyUserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
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

        if (!$user->hasPermission($permission)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
