<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomUnauthorizedException;
use App\Models\Employees\UserCompanyFolder;
use App\Models\Misc\User;
use App\Traits\JSONResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyUserPermission
{
    use JSONResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @throws CustomUnauthorizedException
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
            return $this->errorResponse('Vous n\'êtes pas connecté', 401);
        }

        if (!$user->hasPermission($permission)) {
            throw new CustomUnauthorizedException();
        }

        return $next($request);
    }
}
