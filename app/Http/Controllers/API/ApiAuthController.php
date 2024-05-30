<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyFolder;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiAuthController extends Controller
{
    public function getUser()
    {
        $user = Auth::user()->load(['employee.informations', 'employee.folders', 'employee.folders.company', 'employee.folders.mappings']);
        $userArray = $user->toArray();
        if ($user->employee){
            $employeeArray = $user->employee->toArray();
            $employee = array_merge($userArray, $employeeArray);
            return response()->json($employee);
        }else{
            $companies = Company::with('folders', 'folders.mappings')->get();
            $user = [
                'civility' => $user->civility,
                'email' => $user->email,
                'firstname' => $user->firstname,
                'id' => $user->id,
                'lastname' => $user->lastname,
                'companies' => $companies
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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'civility' => 'required',
            'lastname' => 'required',
            'firstname' => 'required',
            'is_employee' => 'required|boolean',
            'is_company_referent' => 'nullable|boolean',
            'is_folder_referent' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Créer un nouvel utilisateur
            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'civility' => $request->civility,
                'lastname' => $request->lastname,
                'firstname' => $request->firstname
            ]);

            // Si l'utilisateur est un employé, créez une entrée correspondante dans la table employees
            if ($request->is_employee) {
                $employee = new Employee([
                    'user_id' => $user->id,
                    'is_company_referent' => $request->is_company_referent ?? false,
                    'is_folder_referent' => $request->is_folder_referent ?? false
                ]);
                $user->employee()->save($employee);
                return response()->json(['message' => 'Employé créé avec succès'], 200);

            }else{
                return response()->json(['message' => 'Utilisateur créé avec succès'], 200);
            }

        } catch (\Exception $e) {
            // Gestion des erreurs
            return response()->json(['error' => 'Une erreur est survenue lors de la création de l\'utilisateur.'], 500);
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
