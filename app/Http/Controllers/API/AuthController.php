<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\Company;
use App\Models\Employees\Employee;
use App\Models\Employees\EmployeeInfo;
use App\Models\Misc\Role;
use App\Models\Misc\User;
use App\Models\Misc\UserModulePermission;
use App\Models\Modules\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends Controller
{
    public function getUser()
    {
        $user = Auth::user();
        $user->load([
            'folders.modules.module',
            'companies',
            'folders.mappings',
            'folders.software',
        ]);

        $folders = $user->folders;
        $roles = $user->getRoleNames();

        if ($roles->contains('client')) {
            // Ajoute has_access dans l'objet module
            $folders->each(function ($folder) {
                $folder->modules->each(function ($folderModule) {
                    $folderModule->module->has_access = $folderModule->has_access;
                });
            });

            $folderIds = $folders->pluck('id')->toArray();
            $companyIds = $user->companies->pluck('id')->toArray();

            $modules = Module::whereIn('id', function ($query) use ($user, $folderIds, $companyIds) {
                $query->select('module_id')
                    ->from('user_module_permissions')
                    ->where('user_id', $user->id)
                    ->whereIn('module_id', function ($subQuery) use ($folderIds) {
                        $subQuery->select('module_id')
                            ->from('company_folder_module_access')
                            ->whereIn('company_folder_id', $folderIds)
                            ->where('has_access', true);
                    })
                    ->whereIn('module_id', function ($subQuery) use ($companyIds) {
                        $subQuery->select('module_id')
                            ->from('company_module_access')
                            ->whereIn('company_id', $companyIds)
                            ->where('has_access', true);
                    });
            })->with('permissions')->get();
            $modules = $modules->map(function ($module) {
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'permissions' => $module->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->permission_id,
                            'name' => $permission->name,
                            'label' => $permission->label,
                            'company_folder_id' => $permission->company_folder_id
                        ];
                    }),
                ];
            });

            $userResponse = [
                'id' => $user->id,
                'email' => $user->email,
                'civility' => $user->civility,
                'lastname' => $user->lastname,
                'firstname' => $user->firstname,
                'telephone' => $user->telephone,
                'companies' => $user->companies->map(function ($company) use ($folders) {
                    return [
                        'id' => $company->id,
                        'name' => $company->name,
                        'referent_id' => $company->referent_id,
                        'folders' => $folders->filter(function ($folder) use ($company) {
                            return $folder->company_id === $company->id;
                        })->map(function ($folder) {
                            return [
                                'id' => $folder->id,
                                'folder_number' => $folder->folder_number,
                                'folder_name' => $folder->folder_name,
                                'siret' => $folder->siret,
                                'siren' => $folder->siren,
                                'modules' => $folder->modules->map(function ($folderModule) {
                                    return [
                                        'id' => $folderModule->module->id,
                                        'name' => $folderModule->module->name,
                                        'has_access' => $folderModule->has_access,
                                    ];
                                }),
                                'mappings' => $folder->mappings,
                                'software' => $folder->software,
                            ];
                        }),
                    ];
                }),
                'modules' => $modules,
                'roles' => $roles,
            ];

        } else {
            $companies = Company::with(['folders.software', 'folders.mappings', 'folders.users', 'folders.users.modules.permissions'])->get();

            $userResponse = [
                'id' => $user->id,
                'email' => $user->email,
                'civility' => $user->civility,
                'lastname' => $user->lastname,
                'firstname' => $user->firstname,
                'telephone' => $user->telephone,
                'roles' => $roles,
                'companies' => $companies->map(function ($company) {
                    return [
                        'id' => $company->id,
                        'name' => $company->name,
                        'description' => $company->description,
                        'folders' => $company->folders->map(function ($folder) {
                            return [
                                'id' => $folder->id,
                                'folder_number' => $folder->folder_number,
                                'folder_name' => $folder->folder_name,
                                'siret' => $folder->siret,
                                'siren' => $folder->siren,
                                'mappings' => $folder->mappings,
                                'notes' => $folder->notes,
                                'employees' => $folder->users->map(function ($user) {
                                    return [
                                        "id" => $user->id,
                                        "email" => $user->email,
                                        "civility" => $user->civility,
                                        "lastname" => $user->lastname,
                                        "firstname" => $user->firstname,
                                        "telephone" => $user->telephone,
                                        'modules' => $user->modules->map(function ($module) {
                                            return [
                                                'id' => $module->id,
                                                'name' => $module->name,
                                                'permissions' => $module->permissions->map(function ($permission) {
                                                    return [
                                                        'id' => $permission->permission_id,
                                                        'name' => $permission->name,
                                                        'label' => $permission->label,
                                                    ];
                                                }),
                                            ];
                                        }),
                                    ];
                                }),
                                'software' => $folder->software,
                            ];
                        }),
                    ];
                }),
            ];

        }
        return response()->json($userResponse);
    }



    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email ou mot de passe invalides'
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $user = Auth::user();
        $token = $user->createToken('token')->plainTextToken;
        $cookie = cookie('jwt', $token, 60 * 24)->withHttpOnly(true); // 1 day

        return response()->json([
            'message' => 'Connexion réussie'
        ])->withCookie($cookie);
    }

    public function register(Request $request)
    {
        // Valider les données d'entrée
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'civility' => 'required',
            'lastname' => 'required',
            'firstname' => 'required',
            'telephone' => 'nullable|string|min:10|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Creation du nouvel utilisateur
            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'civility' => $request->civility,
                'lastname' => $request->lastname,
                'firstname' => $request->firstname,
                'telephone' => $request->telephone
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
            return response()->json(['message' => 'Utilisateur créé avec succès'], 200);

        } catch (\Exception $e) {
            // Gestion des erreurs
            return response()->json(['error' => 'Une erreur est survenue lors de la création de l\'utilisateur.'], 500);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fields = ['email', 'civility', 'lastname', 'firstname', 'telephone'];

        $updateData = [];

        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $updateData[$field] = $request->input($field);
            }
        }

        if ($user->update($updateData)){
            return response()->json(['user' => $user, 'status' => 204]);
        }else{
            return response()->json(['message' => 'Erreur lors de la mise à jour']);
        }
    }

    public function deleteUser($id){

        $userToDelete = User::where('id', $id)->delete();
        if ($userToDelete) {
            return response()->json(['message'=> 'Utilisateur supprimé du dossier avec succès']);
        }else{
            return response()->json(['message'=> 'Erreur lors de la suppression de l\'utilisteur']);
        }
    }

    protected function logout()
    {
        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'Success',
            'status' => 'success'
        ])->withCookie($cookie);

    }
}
