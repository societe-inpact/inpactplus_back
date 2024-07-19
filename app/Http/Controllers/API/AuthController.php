<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\Company;
use App\Models\Employees\Employee;
use App\Models\Employees\EmployeeInfo;
use App\Models\Misc\Role;
use App\Models\Misc\User;
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
            'modules' => function ($query) use ($user) {
                $query->whereHas('companyModuleAccess', function ($query) use ($user) {
                    $query->where('has_access', true)
                        ->whereIn('company_id', $user->companies->pluck('id')->toArray());
                })->with('permissions');
            },
            'companies',
            'folders',
            'folders.company',
            'folders.mappings',
            'folders.software']);

        $roles = Auth::user()->getRoleNames();

        // Si l'utilisateur est un client Inpact
        if ($roles->contains('client')) {
            $folders = $user->folders;
            foreach ($folders as &$folder) {
                if (isset($folder['mappings'])) {
                    $folder['mappings'] = $folder['mappings']['data'];
                }
            }

            $user = [
                'id' => $user->id,
                'email' => $user->email,
                'civility' => $user->civility,
                'lastname' => $user->lastname,
                'firstname' => $user->firstname,
                'telephone' => $user->telephone,
                'companies' => $user->companies->map(function ($company) {
                    return [
                        'id' => $company['id'],
                        'name' => $company['name'],
                        'description' => $company['description'],
                        'folders' => $company['folders']->map(function ($folder) {
                            if (isset($folder['mappings'])) {
                                return [
                                    'id' => $folder['id'],
                                    'folder_number' => $folder['folder_number'],
                                    'folder_name' => $folder['folder_name'],
                                    'siret' => $folder['siret'],
                                    'siren' => $folder['siren'],
                                    'mappings' => [
                                        'id' => $folder['mappings']['id'],
                                        'data' => $folder['mappings']['data'],
                                    ],
                                    'notes' => $folder['notes'],
                                    'software' => $folder['software'],
                                ];
                            } else {
                                return [
                                    'id' => $folder['id'],
                                    'folder_number' => $folder['folder_number'],
                                    'folder_name' => $folder['folder_name'],
                                    'siret' => $folder['siret'],
                                    'siren' => $folder['siren'],
                                    'mappings' => [],
                                    'notes' => $folder['notes'],
                                    'software' => $folder['software'],
                                ];
                            }
                        }),
                    ];
                }),
                'modules' => $user->modules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'name' => $module->name,
                        'permissions' => $module->permissions->map(function ($permission) {
                            return [
                                'name' => $permission->permission->name,
                            ];
                        }),
                    ];
                }),
                'roles' => $roles
            ];
        } else {
            $companies = Company::with(['folders.software', 'folders.mappings'])->get();
            $modules = Module::all();
            $user = [
                'civility' => $user->civility,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'id' => $user->id,
                'lastname' => $user->lastname,
                'telephone' => $user->telephone,
                'roles' => $roles,
                'modules' => $modules,
                'companies' => $companies->map(function ($company) {
                    return [
                        'id' => $company['id'],
                        'name' => $company['name'],
                        'description' => $company['description'],
                        'folders' => $company['folders']->map(function ($folder) {
                            if (isset($folder['mappings'])) {
                                return [
                                    'id' => $folder['id'],
                                    'folder_number' => $folder['folder_number'],
                                    'folder_name' => $folder['folder_name'],
                                    'siret' => $folder['siret'],
                                    'siren' => $folder['siren'],
                                    'mappings' => [
                                        'id' => $folder['mappings']['id'],
                                        'data' => $folder['mappings']['data'],
                                    ],
                                    'notes' => $folder['notes'],
                                    'software' => $folder['software'],
                                ];
                            } else {
                                return [
                                    'id' => $folder['id'],
                                    'folder_number' => $folder['folder_number'],
                                    'folder_name' => $folder['folder_name'],
                                    'siret' => $folder['siret'],
                                    'siren' => $folder['siren'],
                                    'mappings' => [],
                                    'notes' => $folder['notes'],
                                    'software' => $folder['software'],
                                ];
                            }
                        }),
                    ];
                }),
            ];

        }
        return response()->json($user);
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
                $user->assignRole('client');
            } else {
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

    protected function logout()
    {
        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'Success',
            'status' => 'success'
        ])->withCookie($cookie);

    }
}
