<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn() => ['ok' => true]);
Route::get('/health-any-auth', fn() => ['ok' => true])->middleware(['auth:api', 'can:view-health']);
Route::get('/health-admin', fn() => ['ok' => true])->middleware(['auth:api', 'can:view-health-admin']);



Route::prefix('posts')->group(function () {

    Route::middleware(['throttle:api', 'auth:api', 'role:viewer,editor,admin'])->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('{post}', [PostController::class, 'show']);
    });

    //Escritor o administrador
    Route::middleware(['throttle:api', 'auth:api', 'role:editor,admin'])->group(function () {
        Route::post('/', [PostController::class, 'store'])->middleware('scopes:posts.write');
        // can:action,model
        Route::put('{post}', [PostController::class, 'update'])->middleware(['scopes:posts.write', 'can:update,post']);
        Route::delete('{post}', [PostController::class, 'destroy'])->middleware(['can:delete,post']);;
        Route::post('{post}/restore', [PostController::class, 'restore'])
            ->middleware('scopes:posts.write');
    });
});

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signup']);

    Route::middleware(['auth:api'])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
