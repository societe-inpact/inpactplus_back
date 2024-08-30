<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\InpactResource;
use App\Http\Resources\ReferentResource;
use App\Models\Companies\Company;
use App\Models\Employees\Employee;
use App\Models\Employees\EmployeeInfo;
use App\Models\Misc\Role;
use App\Models\Misc\User;
use App\Models\Misc\UserModulePermission;
use App\Models\Modules\Module;
use App\Traits\JSONResponseTrait;
use App\Traits\ModuleRetrievingTrait;
use App\Traits\UserPermissionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends Controller
{
    use ModuleRetrievingTrait;
    use JSONResponseTrait;

    public function getUser()
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
            'folders.referent',
            'company'
        ])->find(Auth::id());

        if (!$user) {
            return $this->errorResponse('Vous n\'êtes pas connecté', 401);
        }

        $roles = $user->roles->pluck('name')->toArray();

        return match (true) {
            in_array('client', $roles) => new ClientResource($user),
            in_array('referent', $roles) => new ReferentResource($user),
            in_array('inpact', $roles) => new InpactResource($user),
            default => $this->errorResponse('Vous n\'êtes pas autorisé', 403),
        };
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Email ou mot de passe invalides', 401);
        }
        $user = Auth::user();
        $token = $user->createToken('token')->plainTextToken;
        $expiresAt = now()->addDays(1); // Le token expire dans 1 jour

        $user->tokens()->where('name', 'token')->update(['expires_at' => $expiresAt]);

        $cookie = cookie('jwt', $token, 1440)->withHttpOnly(); // Token valable pendant 24h

        return $this->successResponse('', 'Connexion réussie')->withCookie($cookie);
    }

    public function register(Request $request)
    {
        // Validation du nouvel utilisateur
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'civility' => 'required',
            'lastname' => 'required',
            'firstname' => 'required',
            'telephone' => 'nullable|string|min:10|max:10',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        try {
            // Création du nouvel utilisateur
            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'civility' => $request->civility,
                'lastname' => $request->lastname,
                'firstname' => $request->firstname,
                'telephone' => $request->telephone,
            ]);

            // Assignation du rôle et des permissions
            if ($request->is_employee) {
                if (!Role::where('name', 'client')->exists()) {
                    Role::create(['name' => 'client', 'guard_name' => 'web']);
                }
                $user->assignRole('client');
            } else {
                if (!Role::where('name', 'inpact')->exists()) {
                    Role::create(['name' => 'inpact', 'guard_name' => 'web']);
                }
                $user->assignRole('inpact');
            }
            return $this->successResponse($user, 'Utilisateur créé avec succès', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Une erreur est survenue lors de la création de l\'utilisateur.', 500);
        }
    }


    protected function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|unique:users,email,' . $id,
            'civility' => 'nullable',
            'lastname' => 'nullable',
            'firstname' => 'nullable',
            'telephone' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $fields = ['email', 'civility', 'lastname', 'firstname', 'telephone'];

        $updateData = [];

        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $updateData[$field] = $request->input($field);
            }
        }

        if ($user->update($updateData)) {
            return $this->successResponse($user, 'Utilisateur mis à jour avec succès', 204);
        } else {
            return $this->errorResponse('Erreur lors de la mise à jour de l\'utilisateur', 500);
        }
    }

    public function deleteUser($id)
    {
        $userToDelete = User::where('id', $id)->delete();
        // TODO: Gérer la condition si l'user appartient à une company, ou un dossier
        if ($userToDelete) {
            return $this->successResponse('', 'Utilisateur supprimé avec succès', 204);
        } else {
            return $this->errorResponse('Erreur lors de la suppression de l\'utilisateur', 500);
        }
    }

    protected function logout()
    {
        $user = Auth::user();
        $cookie = Cookie::forget('jwt');
        $user->tokens()->delete();
        return $this->successResponse('', '', 204)->withCookie($cookie);;
    }
}
