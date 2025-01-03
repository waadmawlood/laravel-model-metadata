<?php

namespace Waad\Metadata\Tests\App\Models;

use Illuminate\Database\Eloquent\Model;
use Waad\Metadata\Traits\HasOneMetadata;

class Company extends Model
{
    use HasOneMetadata;

    protected $fillable = [
        'name',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}
