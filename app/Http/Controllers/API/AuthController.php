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
use App\Traits\ModuleRetrievingTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends Controller
{
    use ModuleRetrievingTrait;

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
            'folders',
            'company'
        ])->find(Auth::id());
        $folders = $user->folders;
        $roles = $user->roles->pluck('name')->toArray();
        if (in_array('inpact', $roles)) {
            return $this->getInpactResponse($user);
        }
        if (in_array('client', $roles)) {
            return $this->getClientResponse($user, $folders, $roles);
        }
        if (in_array('referent', $roles)) {
            return $this->getReferentResponse($user, $folders, $roles);
        }
        if (in_array('client', $roles) && in_array('referent', $roles)) {
            return $this->getClientResponse($user, $folders, $roles);
        }
    }

    private function getClientResponse($user, $folders, $roles)
    {
        $company = $user->company;
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'civility' => $user->civility,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'telephone' => $user->telephone,
            'roles' => $roles,
            'modules_access' => $user->modules(),
            'companies' => [
                'id' => $company->id,
                'name' => $company->name,
                'description' => $company->description,
                'referent' => $company->referent,
                'folders' => $folders->map(function ($folder) use ($company) {
                    return [
                        'id' => $folder->id,
                        'folder_number' => $folder->folder_number,
                        'folder_name' => $folder->folder_name,
                        'referent' => $folder->referent,
                        'siret' => $folder->siret,
                        'siren' => $folder->siren,
                        'notes' => $folder->notes,
                        'interfaces' => $folder->interfaces,
                        'mappings' => $folder->mappings,
                        'modules_access' => $folder->modules->map(function ($module) {
                            return [
                                'id' => $module->id,
                                'name' => $module->name,
                                'label' => $module->label,
                            ];
                        }),
                    ];
                })
            ]
        ]);
    }

    private function getReferentResponse($user, $folders)
    {
        $company = $user->company;
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'civility' => $user->civility,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'telephone' => $user->telephone,
            'access_modules' => $user->modules(),
            'roles' => $user->roles->map(function ($role) {
                return [
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'name' => $permission->name,
                            'label' => $permission->label,
                        ];
                    })
                ];
            }),
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'description' => $company->description,
                'referent' => $company->referent,
                'employees' => $company->employees,
                'modules_access' => $company->modules,
                'folders' => $folders->map(function ($folder) use ($company) {
                    return [
                        'id' => $folder->id,
                        'folder_number' => $folder->folder_number,
                        'folder_name' => $folder->folder_name,
                        'referent' => $folder->referent,
                        'siret' => $folder->siret,
                        'siren' => $folder->siren,
                        'notes' => $folder->notes,
                        'employees' => $folder->employees,
                        'interfaces' => $folder->interfaces,
                        'mappings' => $folder->mappings,
                        'modules_access' => $folder->modules->map(function ($module) {
                            return [
                                'id' => $module->id,
                                'name' => $module->name,
                                'label' => $module->label,
                                'permissions' => $module->userPermissions->map(function ($permission) {
                                    return [
                                        'id' => $permission->id,
                                        'folder' => $permission->folder,
                                        'name' => $permission->name,
                                        'label' => $permission->label,
                                    ];
                                })->toArray(),
                            ];
                        }),
                    ];
                })
            ]
        ]);
    }

    private function getInpactResponse($user)
    {
        $companies = Company::with(['folders', 'folders.interfaces', 'folders.mappings', 'folders.employees'])->get();
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'civility' => $user->civility,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'telephone' => $user->telephone,
            'roles' => $user->roles->map(function ($role) {
                return [
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'name' => $permission->name,
                            'label' => $permission->label,
                        ];
                    })
                ];
            }),
            'companies' => $companies->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'description' => $company->description,
                    'referent' => $company->referent,
                    'employees' => $company->employees,
                    'modules_access' => $company->modules,
                    'folders' => $company->folders->map(function ($folder) use ($company) {
                        return [
                            'id' => $folder->id,
                            'folder_number' => $folder->folder_number,
                            'folder_name' => $folder->folder_name,
                            'referent' => $folder->referent,
                            'siret' => $folder->siret,
                            'siren' => $folder->siren,
                            'notes' => $folder->notes,
                            'interfaces' => $folder->interfaces,
                            'employees' => $folder->employees,
                            'modules_access' => $folder->modules->map(function ($module) {
                                return [
                                    'id' => $module->id,
                                    'name' => $module->name,
                                    'label' => $module->label,
                                ];
                            }),
                            'mappings' => $folder->mappings,
                        ];
                    })
                ];
            }),
        ]);
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
            return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $user], 200);

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

        if ($user->update($updateData)) {
            return response()->json(['user' => $user, 'status' => 204]);
        } else {
            return response()->json(['message' => 'Erreur lors de la mise à jour']);
        }
    }

    public function deleteUser($id)
    {

        $userToDelete = User::where('id', $id)->delete();
        if ($userToDelete) {
            return response()->json(['message' => 'Utilisateur supprimé du dossier avec succès']);
        } else {
            return response()->json(['message' => 'Erreur lors de la suppression de l\'utilisteur']);
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
