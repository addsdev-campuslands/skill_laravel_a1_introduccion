<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_crea_category_con_slug_valido(): void
    {
        $category = Category::factory()->create();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        $this->assertEquals(Str::slug($category->name), $category->slug);
    }


    public function test_category_posts_many_to_many(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create();

        $category->posts()->attach($post->id);

        $this->assertCount(1, $category->posts()->get());

        $this->assertEquals($post->id, $category->posts()->first()->id);
    }

    public function test_no_duplicado_en_pivote()
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create();

        // Si tu pivote tiene unique([post_id, category_id]), esto se mantiene
        $category->posts()->syncWithoutDetaching([$post->id]);
        $category->posts()->syncWithoutDetaching([$post->id]);

        $this->assertCount(1, $category->posts()->get());
    }
}
