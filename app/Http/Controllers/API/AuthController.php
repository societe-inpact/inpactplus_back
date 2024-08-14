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
        $user = User::with([
            'folders.modules',
            'folders.company',
            'folders.company',
            'folders.mappings',
            'folders.software',
            'folders.employees',
            'folders',
        ])->find(Auth::id());

        $folders = $user->folders;
        $role = $user->getRoleNames()->first();

        return match ($role) {
            'client' => $this->getClientResponse($user, $folders, $role),
            'inpact' => $this->getInpactResponse($user, $role),
            default => response()->json(['erreur' => 'il manque le rôle', 'status' => 500]),
        };
    }

    private function getClientResponse($user, $folders, $role)
    {

        $companyOfFolder = $folders->first()->company;
        $folderIds = $folders->pluck('id')->toArray();
        $companyId = $companyOfFolder->pluck('id')->toArray();

        $userModulesAccess = Module::whereIn('id', function ($query) use ($user, $folderIds, $companyId) {
            $query->select('module_id')
                ->from('user_module_permissions')
                ->where('user_id', $user->id)
                ->whereIn('module_id', function ($subQuery) use ($folderIds) {
                    $subQuery->select('module_id')
                        ->from('company_folder_module_access')
                        ->whereIn('company_folder_id', $folderIds)
                        ->where('has_access', true);
                })
                ->whereIn('module_id', function ($subQuery) use ($companyId) {
                    $subQuery->select('module_id')
                        ->from('company_module_access')
                        ->whereIn('company_id', $companyId)
                        ->where('has_access', true);
                });
        })->with('permissions')->get();

        $modules = $userModulesAccess->map(function ($module) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'permissions' => $module->permissions->map(function ($permission) {
                    return [
                        'id' => $permission->permission_id,
                        'name' => $permission->name,
                        'label' => $permission->label,
                        'company_folder_id' => $permission->company_folder_id,
                    ];
                }),
            ];
        });

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'civility' => $user->civility,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'telephone' => $user->telephone,
            'roles' => $role,
            'modules' => $modules,
            'company' => [
                'id' => $companyOfFolder->id,
                'name' => $companyOfFolder->name,
                'referent' => $companyOfFolder->referent,
                'employees' => $companyOfFolder->getEmployees(),
                'description' => $companyOfFolder->description,
                'modules' => $companyOfFolder->modules->where('has_access'),
                'folders' => $folders->where('company_id', $companyOfFolder->id)->map(function ($folder) {
                    return [
                        'id' => $folder->id,
                        'folder_number' => $folder->folder_number,
                        'folder_name' => $folder->folder_name,
                        'siret' => $folder->siret,
                        'siren' => $folder->siren,
                        'referent' => $folder->employees->firstWhere('id', $folder->referent_id)->only('id', 'lastname', 'firstname', 'telephone', 'email'),
                        'modules' => $folder->modules->map(function ($folderModule) {
                            return [
                                'id' => $folderModule->id,
                                'name' => $folderModule->name,
                                'has_access' => $folderModule->has_access,
                            ];
                        }),
                        'mappings' => $folder->mappings,
                        'software' => $folder->software,
                    ];
                })
            ]
        ]);
    }

    private function getInpactResponse($user, $role)
    {
        $companies = Company::with(['folders.software', 'folders.mappings', 'folders.employees.modules.permissions'])->get();
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'civility' => $user->civility,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'telephone' => $user->telephone,
            'roles' => $role,
            'companies' => $companies->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'referent' => $company->referent,
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
                            'referent' => $folder->referent,
                            'employees' => $folder->employees->map(function ($employee) {
                                return [
                                    "id" => $employee->id,
                                    "email" => $employee->email,
                                    "civility" => $employee->civility,
                                    "lastname" => $employee->lastname,
                                    "firstname" => $employee->firstname,
                                    "telephone" => $employee->telephone,
                                    "role" => $employee->getRoleNames()->first(),
                                    'modules' => $employee->modules->map(function ($module) {
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
        ]);
    }


//    public function getUser()
//    {
//        $user = Auth::user();
//        $user->load([
//            'folders.modules.module',
//            'folders.companies',
//            'folders.mappings',
//            'folders.software',
//            'folders.users',
//        ]);
//
//        $folders = $user->folders;
//        $roles = $user->getRoleNames();
//
//        // Inutile, il y a juste la relation du modèle à définir (cf: ligne 108)
//
//        // vérification s'il y a un referent_id dans le dossier si pas referent companies
////        foreach ($folders as $folder) {
////            $referent_id = $folder->referent_id;
////
////            if($referent_id) {
////                $folder['referent_id'] = $referent_id ;
////            } else {
////                $folder['referent_id'] = $folder['companies']->referent_id ;
////            }
////        }
//
//        // $role = $roles[0]; // Création de la variable inutile - Utiliser la fonction first (convention Laravel) plutôt que l'index du tableau
//        switch ($roles->first()) {
//
//            case 'client' :
//                // reprendre la compagnie du premier dossier
//                $companies = $folders[0]['companies'];
//                // Ajoute has_access dans l'objet module
//                $folders->each(function ($folder) {
//                    $folder->modules->each(function ($folderModule) {
//                        $folderModule->module->has_access = $folderModule->has_access;
//                    });
//                });
//
//                $folderIds = $folders->pluck('id')->toArray();
//                $companyId = $companies->pluck('id')->toArray();
//
//                $modules = Module::whereIn('id', function ($query) use ($user, $folderIds, $companyId) {
//                    $query->select('module_id')
//                        ->from('user_module_permissions')
//                        ->where('user_id', $user->id)
//                        ->whereIn('module_id', function ($subQuery) use ($folderIds) {
//                            $subQuery->select('module_id')
//                                ->from('company_folder_module_access')
//                                ->whereIn('company_folder_id', $folderIds)
//                                ->where('has_access', true);
//                        })
//                        ->whereIn('module_id', function ($subQuery) use ($companyId) {
//                            $subQuery->select('module_id')
//                                ->from('company_module_access')
//                                ->whereIn('company_id', $companyId)
//                                ->where('has_access', true);
//                        });
//                })->with('permissions')->get();
//                $modules = $modules->map(function ($module) {
//                    return [
//                        'id' => $module->id,
//                        'name' => $module->name,
//                        'permissions' => $module->permissions->map(function ($permission) {
//                            return [
//                                'id' => $permission->permission_id,
//                                'name' => $permission->name,
//                                'label' => $permission->label,
//                                'company_folder_id' => $permission->company_folder_id
//                            ];
//                        }),
//                    ];
//                });
//
//                $userResponse = [
//                    'id' => $user->id,
//                    'email' => $user->email,
//                    'civility' => $user->civility,
//                    'lastname' => $user->lastname,
//                    'firstname' => $user->firstname,
//                    'telephone' => $user->telephone,
//                    'companies' => [
//                        // reprise des informations de la compagnie
//                        'id' => $companies->id,
//                        'name' => $companies->name,
//                        'referent' => $companies->referent,
//                        // reprise des informations des folders
//                        'folders' => $folders->filter(function ($folder) use ($companies) {
//                            return $folder->company_id === $companies->id;
//                        })->map(function ($folder) {
//                            return [
//                                'id' => $folder->id,
//                                'folder_number' => $folder->folder_number,
//                                'folder_name' => $folder->folder_name,
//                                'siret' => $folder->siret,
//                                'siren' => $folder->siren,
//                                'referent' => $folder->users->filter(function ($user) use ($folder) {
//                                    return $user->id === $folder->referent_id;
//                                })->map(function ($user) {
//                                    return [
//                                        'id' => $user->id,
//                                        'lastname' => $user->lastname,
//                                        'firstname' => $user->firstname,
//                                        'telephone' => $user->telephone,
//                                        'email' => $user->email,
//                                    ];
//                                }),
//                                'modules' => $folder->modules->map(function ($folderModule) {
//                                    return [
//                                        'id' => $folderModule->module->id,
//                                        'name' => $folderModule->module->name,
//                                        'has_access' => $folderModule->has_access,
//                                    ];
//                                }),
//                                'mappings' => $folder->mappings,
//                                'software' => $folder->software,
//                            ];
//                        }),
//                    ],
//                    'modules' => $modules,
//                    'roles' => $roles,
//                ];
//                break;
//            case 'inpact' :
//                $companies = Company::with(['folders.software', 'folders.mappings', 'folders.users', 'folders.users.modules.permissions'])->get();
//
//                $userResponse = [
//                    'id' => $user->id,
//                    'email' => $user->email,
//                    'civility' => $user->civility,
//                    'lastname' => $user->lastname,
//                    'firstname' => $user->firstname,
//                    'telephone' => $user->telephone,
//                    'roles' => $roles,
//                    'companies' => $companies->map(function ($company) {
//                        return [
//                            'referent' => $company->referent,
//                            'id' => $company->id,
//                            'name' => $company->name,
//                            'description' => $company->description,
//                            'folders' => $company->folders->map(function ($folder) {
//                                return [
//                                    'id' => $folder->id,
//                                    'folder_number' => $folder->folder_number,
//                                    'folder_name' => $folder->folder_name,
//                                    'siret' => $folder->siret,
//                                    'siren' => $folder->siren,
//                                    'mappings' => $folder->mappings,
//                                    'notes' => $folder->notes,
//                                    'referent' => $folder->referent,
////                                'referent' => $folder->users->filter(function($user) use($folder){
////                                    return $user->id === $folder->referent_id;
////                                })->map(function($user){
////                                    return [
////                                        'id'=> $user->id,
////                                        'lastname' => $user->lastname,
////                                        'firstname' => $user->firstname,
////                                        'telephone' => $user->telephone,
////                                        'email' => $user->email,
////                                    ];
////                                }),
//                                    'employees' => $folder->users->map(function ($user) {
//                                        return [
//                                            "id" => $user->id,
//                                            "email" => $user->email,
//                                            "civility" => $user->civility,
//                                            "lastname" => $user->lastname,
//                                            "firstname" => $user->firstname,
//                                            "telephone" => $user->telephone,
//                                            'modules' => $user->modules->map(function ($module) {
//                                                return [
//                                                    'id' => $module->id,
//                                                    'name' => $module->name,
//                                                    'permissions' => $module->permissions->map(function ($permission) {
//                                                        return [
//                                                            'id' => $permission->permission_id,
//                                                            'name' => $permission->name,
//                                                            'label' => $permission->label,
//                                                        ];
//                                                    }),
//                                                ];
//                                            }),
//                                        ];
//                                    }),
//                                    'software' => $folder->software,
//                                ];
//                            }),
//                        ];
//                    }),
//                ];
//
//                break;
//            default :
//                $userResponse = ['erreur' => 'il manque le rôle', 'status' => 500];
//
//        }
//        return response()->json($userResponse);
//
//    }


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
