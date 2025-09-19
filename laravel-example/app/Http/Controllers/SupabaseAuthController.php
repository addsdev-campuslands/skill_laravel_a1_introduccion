<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SupabaseAuthController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token faltante'], 401);
        }

        // Validar el token con Supabase
        $response = Http::withToken($token)->get(env('VITE_SUPABASE_URL') . '/auth/v1/user');

        if ($response->failed()) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $data = $response->json();
        $email = $data['email'] ?? null;
        $name = $data['user_metadata']['name'] ?? 'Sin nombre';

        if (!$email) {
            return response()->json(['message' => 'Email no disponible'], 422);
        }

        // Buscar o crear el usuario
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt(str()->random(32))]
        );

        Auth::login($user);

        return response()->json(['message' => 'Autenticado'], 200);
    }
}

