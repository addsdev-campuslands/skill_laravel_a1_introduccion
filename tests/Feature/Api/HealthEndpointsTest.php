<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\AuthTestCase;

class HealthEndpointsTest extends AuthTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }
    /**
     * Verificar el endpoint publico de Health
     */
    public function test_health_publico_reponse_ok(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_any_auth_require_authentication_y_gate_view_health(): void
    {
        $viewer = User::factory()->withRole('viewer')->create();

        Passport::actingAs($viewer, ['posts.read']);

        $this->getJson('/api/health-any-auth')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_health_auth_require_authentication_y_gate_view_health_unauthorized(): void
    {
        $viewer = User::factory()->withRole('viewer')->create();

        Passport::actingAs($viewer, ['posts.read']);

        $this->getJson('/api/health-admin')
            ->assertForbidden()
            ->assertJsonStructure(['status', 'message', 'errors']);
    }
}
