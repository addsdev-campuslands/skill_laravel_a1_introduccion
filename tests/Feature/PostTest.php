<?php

namespace Tests\Feature;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_crea_post_valido_y_casts_funcionan()
    {
        $tags = ['laravel', 'php'];
        $meta = ['seo_title' => 'title', 'seo_desc' => 'description'];

        $post = Post::factory()->create([
            'tags' => $tags,
            'meta' => $meta
        ]);

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
        $this->assertIsArray($post->tags);
        $this->assertSame($tags, $post->tags);
        $this->assertIsArray($post->meta);
        $this->assertSame($meta, $post->meta);
        $this->assertInstanceOf(\Carbon\CarbonInterface::class, $post->created_at);
    }

    public function test_estados_de_factory_post_published_y_draft()
    {
        //use Carbon\Carbon;
        Carbon::setTestNow('2025-09-11 10:00:00');
        //now()

        $published = Post::factory()->published()->create();

        $this->assertEquals('published', $published->status);
        $this->assertNotNull($published->published_at);

        $this->assertTrue($published->published_at->lessThanOrEqualTo(now()));

        $draft = Post::factory()->draft()->create();
        $this->assertEquals('draft', $draft->status);
        $this->assertNull($draft->published_at);

        Carbon::setTestNow();
    }
}
