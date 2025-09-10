<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SupabaseAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Obtener el token de la cabecera de autorización.
        $authorizationHeader = $request->header('Authorization');

        if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
            // Si no hay token de Supabase, permite que la petición continúe (puede ser una ruta pública).
            return $next($request);
        }

        $token = substr($authorizationHeader, 7);
        $supabaseSecret = env('SUPABASE_JWT_SECRET');

        try {
            // 2. Decodificar el token JWT usando la clave secreta de Supabase.
            $decoded = JWT::decode($token, new Key($supabaseSecret, 'HS256'));

            // 3. Extraer los datos del usuario del token.
            $userEmail = $decoded->email;
            $userName = $decoded->user_metadata->name ?? explode('@', $userEmail)[0];
            $userProvider = $decoded->app_metadata->provider ?? 'email';
            $userProviderId = $decoded->sub; // El 'sub' del token es el ID de usuario en Supabase.

            // 4. Buscar o crear el usuario en tu base de datos de Laravel.
            $user = User::where('email', $userEmail)->first();

            if (!$user) {
                // Si el usuario no existe, lo creamos.
                $user = User::create([
                    'name' => $userName,
                    'email' => $userEmail,
                    'provider' => $userProvider,
                    'provider_id' => $userProviderId,
                    'password' => Hash::make(uniqid()), // Contraseña aleatoria para usuarios de OAuth.
                ]);

                // Asignar el rol por defecto si existe.
                $defaultRole = Role::where('name', 'viewer')->first();
                if ($defaultRole) {
                    $user->roles()->syncWithoutDetaching([$defaultRole->id]);
                }
            }

            // 5. Autenticar al usuario en Laravel.
            Auth::login($user);

            // 6. Continuar con la petición original.
            return $next($request);

        } catch (\Exception $e) {
            // 7. Manejar errores de token inválido.
            Log::error('Supabase authentication error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid Supabase token.'], 401);
        }
    }
}
