<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // if (Schema::hasTable('posts')) {
        //     return;
        // }

        Schema::table('posts', function (Blueprint $table) {
            // Agregar las nuevas columnas
            $table->dropColumn([
                'status'
            ]);

            $table->string('slug', 220)->unique();
            $table->enum('status', ['draft', 'published', 'archived', 'default'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('cover_image')->nullable();

            $table->json('meta')->nullable(); // {"seo_title": "....", ....}
            $table->json('tags')->nullable(); // ['php', 'git', .....]

            $table->softDeletes();

            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            //Eliminar los cambios en el orden contrario
            $columns = [
                'slug',
                'published_at',
                'cover_image',
                'meta',
                'tags',
                'deleted_at'
            ];

            for ($i = 0; $i < count($columns); $i++) {
                $column = $columns[$i];
                if (Schema::hasColumn('posts', $column)) {
                    $table->dropColumn($column);
                }
            }

            //$table->dropIndex(['status', 'published_at']);

            if (Schema::hasIndex('posts', ['status', 'published_at', 'unique'])) {
                $table->dropIndex('posts_status_published_at_index');
            }


            $table->boolean('status')->change();
        });
    }
};
