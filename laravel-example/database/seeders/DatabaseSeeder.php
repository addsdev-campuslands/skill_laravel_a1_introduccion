<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\PostSeeder;
use App\Models\User;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Primero los roles
        $this->call(RoleSeeder::class);

        // Luego el usuario
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Asignar rol viewer
        $viewerRole = Role::where('name', 'viewer')->first();
        if ($viewerRole) {
            $user->roles()->syncWithoutDetaching([$viewerRole->id]);
        }

        // Finalmente los posts
        $this->call(PostSeeder::class);
    }
}
