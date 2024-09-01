<?php

namespace App\Http\Middleware;

use App\Models\Employees\UserCompanyFolder;
use App\Models\Misc\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyUserFolderAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::with([
            'folders.modules',
            'folders.modules.companyFolderAccess',
            'folders.modules.companyAccess',
            'folders.modules.userAccess',
            'folders.modules.userPermissions',
            'folders.company',
            'folders.mappings',
            'folders.interfaces',
            'folders.employees',
            'folders.referent',
            'folders',
            'company'
        ])->find(Auth::id());


        if (!$user) {
            return $this->errorResponse('Vous n\'êtes pas connecté', 401);
        }

        $folderId = $request->route('company_folder_id');

        $userIsFolderReferent = $user->folders()->where('company_folders.id', $folderId) // Précisez la table
        ->whereHas('referent', function ($query) use ($user) {
            $query->where('id', $user->id); // Ajoutez également la spécification de la table ici si nécessaire
        })->exists();


        $companyFolderIds= $user->folders->pluck('id')->toArray();
        $userFolderHasAccess = UserCompanyFolder::where('user_id', $user->id)
            ->whereIn('company_folder_id', $companyFolderIds)
            ->where('has_access', true)
            ->exists();

        if ($user->hasRole('inpact') || $userFolderHasAccess || $userIsFolderReferent) {
            return $next($request);
        }
        return $this->errorResponse('Vous n\'avez pas accès à ce dossier', 401);
    }
}
