<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un solo dato con Random haciendo uso de insert
        // DB::table('posts')->insert([
        //     'title'=> Str::random(10),
        //     'content' => Str::random(10).'example@gmail.com',
        //     'status' => true,
        // ]);
        Post::factory()->count(50)->create();
    }
}
