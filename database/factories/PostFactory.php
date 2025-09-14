<?php

namespace Database\Factories;

use App\Models\User;
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
        $title = fake()->unique()->sentence(10);

        return [
            'title' => $title,
            'content' => fake()->paragraph(3, true),
            'slug' => fake()->uuid(),
            'status' => fake()->randomElement(['draft', 'published', 'archived', 'default']),
            'published_at' => now()->subDays(rand(1, 365)),
            'cover_image' => "https://placehold.co/600x400/000000/FFFFFF.png",
            'tags' => fake()->randomElement(['laravel', 'php', 'eloquent', 'testing', 'api'], rand(1, 3)),
            'meta' => [
                'seo_title' => fake()->sentence(3),
                'seo_desc' => fake()->sentence(6),
            ],
            'user_id' => User::factory()
        ];
    }

    public function published(): static
    {
        return $this->state(fn() => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn() => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
