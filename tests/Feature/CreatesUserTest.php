<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreatesUserTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_puede_crear_un_usuario(): void
    {
        $user = User::factory()->create([
            'name' => 'Adrian',
            'email' => 'adrian@example.com',
        ]);

        // Verificar que se creó en la base de datos
        $this->assertDatabaseHas('users', [
            'email' => 'adrian@example.com',
        ]);

        // Verificar que el nombre corresponde
        $this->assertEquals('Adrian', $user->name);
    }
}
