<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'title' => fake()->sentence(10),
            'content' => fake()->paragraph(),
            'slug' => fake()->uuid(),
            'status' => fake()->randomElement(['draft', 'published', 'archived', 'default']),
            'published_at' => fake()->dateTime(),
            'cover_image' => "https://placehold.co/600x400/000000/FFFFFF.png",
            'tags' => [fake()->sentence(2), fake()->sentence(2)],
            'meta' => [
                'seo_title' => fake()->sentence(3),
                'seo_desc' => fake()->sentence(6),
            ]
        ];
    }
}
