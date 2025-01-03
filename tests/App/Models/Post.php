<?php

namespace Waad\Metadata\Tests\App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Metadata\Traits\HasManyMetadata;

class Post extends Model
{
    use HasManyMetadata;

    protected $fillable = [
        'title',
        'content',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}
