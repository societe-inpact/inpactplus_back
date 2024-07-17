<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Companies\Company;
use App\Models\Employees\Employee;
use App\Models\Employees\EmployeeInfo;
use App\Models\Misc\User;
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
        $user = Auth::user()->load([
            'informations',
            'module_convert',
            'module_convert.permissions' ,
            'module_statistics',
            'module_statistics.permissions' ,
            'module_mapping',
            'module_mapping.permissions' ,
            'module_history' ,
            'module_history.permissions' ,
            'folders',
            'folders.company',
            'folders.mappings',
            'folders.software']);

        $role = Auth::user()->getRoleNames()->first();
        $permissions = Auth::user()->getAllPermissions()->pluck('name');
        $userArray = $user->toArray();

        if ($user->employee) {
            $employeeArray = $user->employee->toArray();

            // Parcourir chaque dossier pour extraire uniquement les données 'data' de la relation 'mappings'
            foreach ($employeeArray['folders'] as &$folder) {
                if (isset($folder['mappings'])) {
                    $folder['mappings'] = $folder['mappings']['data'];
                }
            }

            // Fusionner les données de l'employé avec les données des dossiers mises à jour
            $employee = array_merge($userArray, $employeeArray);

            $employee['role'] = $role;
            $employee['permissions'] = $permissions;

            return response()->json($employee);
        } else {
            // Récupérer les informations de l'utilisateur sans les données des dossiers
            $companies = Company::with(['folders.software', 'folders.mappings'])->get();

            $user = [
                'civility' => $user->civility,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'id' => $user->id,
                'lastname' => $user->lastname,
                'role' => $role,
                'permissions' => $permissions,
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

            return response()->json($user);
        }
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
                if ($request->is_company_referent) {
                    $user->assignRole('referent');
                    $user->givePermissionTo('create', 'edit', 'view');
                } elseif ($request->is_folder_referent) {
                    $user->assignRole('referent');
                    $user->givePermissionTo('view', 'edit');
                } else {
                    $user->assignRole('basic');
                    //$user->givePermissionTo('view');
                }
            } else {
                $user->assignRole('admin');
                $user->givePermissionTo(Permission::all());
            }

            // Si l'utilisateur est un employé, créer une entrée correspondante dans la table employees
            if ($request->is_employee) {
                $employeeInfo = EmployeeInfo::create([
                    'employee_code' => $request->employee_code,
                    'RIB' => $request->RIB,
                    'postal_code' => $request->postal_code,
                    'postal_address' => $request->postal_address,
                    'social_security_number' => $request->social_security_number
                ]);

                if ($employeeInfo->save()) {
                    $employee = Employee::create([
                        'user_id' => $user->id,
                        'is_company_referent' => $request->is_company_referent ?? false,
                        'is_folder_referent' => $request->is_folder_referent ?? false,
                        'informations_id' => $employeeInfo->id,
                    ]);

                    $user->employee()->save($employee);
                    return response()->json(['message' => 'Employé créé avec succès'], 200);
                }
            } else {
                return response()->json(['message' => 'Utilisateur créé avec succès'], 200);
            }

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
