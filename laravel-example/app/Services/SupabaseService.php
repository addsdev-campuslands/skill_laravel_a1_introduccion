<?php 

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class SupabaseService
{
    public function verify(string $accessToken): array
    {
        $baseUrl = rtrim(Config::get('services.supabase.url'), '/');
        if (!$baseUrl) {
            throw new \RuntimeException('Supabase URL is not configured.');
        }

        $jwks = Cache::remember('supabase.jwks', 3600, function () use ($baseUrl) {
            $res = Http::get("$baseUrl/auth/v1/keys");
            if (!$res->ok()) {
                throw new \RuntimeException('No se pudieron obtener las claves JWK de Supabase.');
            }
            return $res->json();
        });

        $decoded = (array) JWT::decode(
            $accessToken,
            json_decode(json_encode(JWK::parseKeySet($jwks))),
            null
        );        
        

        return [
            'email' => $decoded['email'] ?? null,
            'name'  => $decoded['user_metadata']['full_name'] ?? ($decoded['name'] ?? null),
            'sub'   => $decoded['sub'] ?? null,
            'raw'   => $decoded,
        ];
    }
}