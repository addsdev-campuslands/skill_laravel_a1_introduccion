<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostConfigTest extends TestCase
{

    public function test_fillable_y_casts_configurados(): void
    {
        $post = new Post;

        $this->assertEqualsCanonicalizing(
            [
                'title',
                'content',
                'slug',
                'status',
                'published_at',
                'cover_image',
                'tags',
                'meta',
                'user_id'
            ],
            $post->getFillable()
        );

        $this->assertArrayHasKey('tags', $post->getCasts());
        $this->assertArrayHasKey('meta', $post->getCasts());
        $this->assertArrayHasKey('published_at', $post->getCasts());
        $this->assertSame('array', $post->getCasts()['tags']);
        $this->assertSame('array', $post->getCasts()['meta']);
    }
}
