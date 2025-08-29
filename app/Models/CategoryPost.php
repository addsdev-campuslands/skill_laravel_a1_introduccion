<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryPost extends Pivot
{
    protected $table = "category_post";

    protected $fillable = [
        'post_id',
        'category_id'
    ];
}
