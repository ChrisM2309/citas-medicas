<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciales invalidas',
            ], 422);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'El usuario esta inactivo',
            ], 403);
        }

        if (! method_exists($user, 'createToken')) {
            return response()->json([
                'message' => 'Laravel Sanctum no esta instalado o configurado. No se puede emitir token de acceso.',
                'user' => new UserResource($user),
            ], 501);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'No autenticado',
            ], 401);
        }

        if (! method_exists($user, 'currentAccessToken')) {
            return response()->json([
                'message' => 'Laravel Sanctum no esta instalado o configurado.',
            ], 501);
        }

        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logout exitoso',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'No autenticado',
            ], 401);
        }

        return new UserResource($user);
    }
}