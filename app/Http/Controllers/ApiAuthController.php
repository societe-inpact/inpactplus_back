<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiAuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Valider les champs email et mot de passe
        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        // Vérifier s'il y a des erreurs de validation
        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        // Rechercher l'utilisateur dans la base de données par son email
        $user = User::where('email', $request->email)->first();

        // Vérifier si l'utilisateur existe et si le mot de passe correspond
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Créer les informations d'identification (credentials)
        $credentials = request(['email', 'password']);

        // Tenter d'authentifier l'utilisateur et générer le token JWT
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }else{
            Auth::login($user);
        }

        // Retourner la réponse avec le token JWT
        return $this->respondWithToken($token);
    }

    public function register (Request $request) {

        $fields = [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ];

        // Valider les données entrantes
        $validator = Validator::make($request->all(), $fields);

        // Vérifier si la validation a échoué
        if ($validator->fails()) {
            $response = [
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ];
            return response($response, 400);
        }

        // Hasher le mot de passe
        $hashedPassword = Hash::make($request->password);

        // Créer l'utilisateur
        $user = User::create([
            'email' => $request->email,
            'password' => $hashedPassword,
        ]);

        $response = [
            'status' => 'success',
            'message' => 'User is created successfully.',
            'data' => $user,
        ];
        return response($response, 201);

    }

    public function logout () {
        Auth::logout();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'status' => 'success',
        ]);
    }
}
