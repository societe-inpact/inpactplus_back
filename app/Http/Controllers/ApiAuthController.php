<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiAuthController extends Controller
{
      public function user()
    {
        return Auth::user();
    }

    public function login(Request $request){
        if (!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'message' => 'Email ou mot de passe invalides'
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $user = Auth::user();
        $token = $user->createToken('token')->plainTextToken;
        $cookie = cookie('jwt', $token, 60 * 24)->withHttpOnly(false); // 1 day

        return response()->json([
            'message' => $token
        ])->withCookie($cookie);
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
        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'Success',
            'status' => 'success'
        ])->withCookie($cookie);

    }
}
