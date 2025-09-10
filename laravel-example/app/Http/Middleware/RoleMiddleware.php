<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            $user = $request->user();

            // Verificar si el usuario está autenticado
            if (!$user) {
                Log::warning('RoleMiddleware: Usuario no autenticado intentando acceder a ruta protegida', [
                    'route' => $request->path(),
                    'method' => $request->method()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Verificar si se proporcionaron roles
            if (empty($roles)) {
                Log::error('RoleMiddleware: No se especificaron roles requeridos', [
                    'route' => $request->path(),
                    'user_id' => $user->id
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de configuración: roles no especificados'
                ], 500);
            }

            // Verificar si el usuario tiene el rol requerido
            if (!$user->hasRole($roles)) {
                Log::warning('RoleMiddleware: Usuario sin permisos suficientes', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'required_roles' => $roles,
                    'user_roles' => $user->roles->pluck('name')->toArray(),
                    'route' => $request->path()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario No Autorizado o Sin Rol'
                ], 403);
            }

            Log::info('RoleMiddleware: Acceso autorizado', [
                'user_id' => $user->id,
                'required_roles' => $roles,
                'route' => $request->path()
            ]);

            return $next($request);
        } catch (\Throwable $e) {
            Log::error('RoleMiddleware: Error inesperado', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'route' => $request->path(),
                'user_id' => $request->user()?->id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor en verificación de roles'
            ], 500);
        }
    }
}