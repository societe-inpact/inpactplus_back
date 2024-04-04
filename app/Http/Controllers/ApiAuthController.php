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

        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if($validate->fails()){
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        // Check email exist
        $user = User::where('email', $request->email)->first();

        // Check password
        if(!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

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

    public function logout (Request $request) {
        var_dump("logout");
        $token = $request->user()->token();
        $token->revoke();
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
