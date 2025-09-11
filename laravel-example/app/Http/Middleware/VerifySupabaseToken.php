<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class VerifySupabaseToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Token no proporcionado'], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key(env('SUPABASE_JWT_SECRET'), 'HS256'));
            $request->attributes->set('supabase_claims', (array) $decoded);
            return $next($request);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token inválido', 'error' => $e->getMessage()], 401);
        }
    }
}
