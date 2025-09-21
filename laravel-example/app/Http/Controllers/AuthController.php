<?php

namespace App\Http\Controllers;

use App\Mail\UserRegisteredMail;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use ApiResponse;
    
    /**
     * @OA\Post(
     *   path="/api/auth/login",
     *   tags={"Auth"},
     *   summary="Login y emisión de token",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="user@test.com"),
     *       @OA\Property(property="password", type="string", minLength=8, example="pa55Word123$.")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="success"),
     *       @OA\Property(property="message", type="string", nullable=true),
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="token_type", type="string", example="Bearer"),
     *         @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1Qi..."),
     *         @OA\Property(property="user", type="object",
     *           @OA\Property(property="email", type="string", example="user@test.com"),
     *           @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"viewer","editor"})
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=401, description="Credenciales inválidas")
     * )
     */

    function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);
        //!Auth::attempt($data)
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Credenciales invalidas', 401);
        }

        $user = $request->user();

        $tokenResult = $user->createToken('api-token', ['posts.read', 'posts.write']);

        $token = $tokenResult->accessToken;

        Mail::to($user->email)->queue(new UserRegisteredMail($user)); //QueueMail::to($user->email)->queue(new UserRegisteredMail($user)); // Mailpit
        Mail::mailer('real')->to($user->email)->queue(new UserRegisteredMail($user)); // Gmail
        
        return $this->success([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => [
                'email' => $user->email,
                'roles' => $user->roles()->pluck('name'),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/signup",
     *   tags={"Auth"},
     *   summary="Registro de usuario",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name","email","password","password_confirmation"},
     *       @OA\Property(property="name", type="string", example="Ada Lovelace"),
     *       @OA\Property(property="email", type="string", format="email", example="ada@example.com"),
     *       @OA\Property(property="password", type="string", minLength=8, example="pa55Word123$."),
     *       @OA\Property(property="password_confirmation", type="string", example="pa55Word123$.")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Creado")
     * )
     */

    function signup(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $defaultRole = Role::where('name', 'viewer')->first();
        if ($defaultRole) {
            $user->roles()->syncWithoutDetaching([$defaultRole->id]);
        }
        return $this->success($user->load('roles'), 'Usuario creado correctamente', 201);
    }

    /**
     * @OA\Get(
     *   path="/api/auth/me",
     *   tags={"Auth"},
     *   summary="Perfil basico",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="OK")
     * )
     */

    function me(Request $request)
    {
        return $this->success("Hellou Camper!");
    }

    /**
     * @OA\Post(
     *   path="/api/auth/logout",
     *   tags={"Auth"},
     *   summary="Cerrar sesión",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="OK")
     * )
     */

    function logout(Request $request)
    {
        return $this->success("Hellou Camper!");
    }
    public function createAdmin(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        Mail::to($user->email)->queue(new UserRegisteredMail($user)); // Mailpit
        Mail::mailer('real')->to($user->email)->queue(new UserRegisteredMail($user)); // Gmail


        return $this->success($user->load('roles'), 'Administrador creado correctamente', 201);
    }


}