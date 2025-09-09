<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

// Rutas de salud y status
Route::get('/health', fn() => ['ok' => true]);

// Las rutas de login y signup ya no son necesarias para Supabase
// La autenticación con Supabase se maneja completamente en el frontend.
// Sin embargo, si quieres mantenerlas para un login tradicional (email/password), puedes hacerlo.
// Pero la lógica de autenticación de Supabase no las usa.
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signup']);

    // Esta ruta protegida por el middleware de Supabase
    // Solo se podrá acceder si el token JWT de Supabase es válido
    Route::middleware(['supabase.auth'])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('posts')->group(function () {

    // Rutas de lectura de posts, ahora protegidas con el middleware de Supabase.
    // OJO: Si usas Laravel Passport y Supabase, tendrías que elegir un método
    // de autenticación. Aquí vamos a asumir que usas Supabase para todo.
    // Reemplazamos 'auth:api' por 'supabase.auth'
    Route::middleware(['throttle:api', 'supabase.auth', 'role:viewer,editor,admin'])->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('{post}', [PostController::class, 'show']);
    });

    // Escritor o administrador
    // Reemplazamos 'auth:api' por 'supabase.auth'
    Route::middleware(['throttle:api', 'supabase.auth', 'role:editor,admin'])->group(function () {
        Route::post('/', [PostController::class, 'store'])->middleware('scopes:posts.write');
        Route::put('{post}', [PostController::class, 'update'])->middleware(['scopes:posts.write', 'can:update,post']);
        Route::delete('{post}', [PostController::class, 'destroy'])->middleware(['can:delete,post']);
        Route::post('{post}/restore', [PostController::class, 'restore'])
            ->middleware('scopes:posts.write');
    });
});
