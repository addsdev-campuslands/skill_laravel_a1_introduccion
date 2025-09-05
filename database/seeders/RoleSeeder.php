<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'viewer'], ['label' => 'Lector']);
        Role::firstOrCreate(['name' => 'editor'], ['label' => 'Editor/Editora']);
        Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrador VIP']);
    }
}
