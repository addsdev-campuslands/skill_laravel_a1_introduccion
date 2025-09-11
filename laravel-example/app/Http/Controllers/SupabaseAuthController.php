<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SupabaseAuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $claims = $request->attributes->get('supabase_claims', []);

        $email = $claims['email'] ?? ($claims['user_metadata']['email'] ?? null);
        $name  = $claims['user_metadata']['full_name'] ?? $claims['name'] ?? 'Usuario';
        $sub   = $claims['sub'] ?? null;
        $provider = $claims['app_metadata']['provider'] ?? 'supabase';

        if (!$email) {
            return $this->error('El token no contiene email', 422);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt(str()->random(32)),
            ]
        );

        if (empty($user->provider) || empty($user->provider_id)) {
            $user->provider = $provider;
            $user->provider_id = $sub;
            $user->save();
        }

        $defaultRole = Role::where('name', 'viewer')->first();
        if ($defaultRole) {
            $user->roles()->syncWithoutDetaching([$defaultRole->id]);
        }

        $tokenResult = $user->createToken('api-token', ['posts.read', 'posts.write']);
        $accessToken = $tokenResult->accessToken;

        return $this->success([
            'token_type' => 'Bearer',
            'access_token' => $accessToken,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'roles' => $user->roles()->pluck('name'),
                'provider' => $provider,
            ],
        ]);
    }
}
