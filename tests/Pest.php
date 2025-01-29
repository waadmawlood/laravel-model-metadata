<?php

use Waad\Metadata\Tests\App\Models\Company;
use Waad\Metadata\Tests\App\Models\Post;
use Waad\Metadata\Tests\TestCase;

uses(TestCase::class)->in('Feature');

function createCompany()
{
    return Company::query()->create([
        'name' => fake()->name(),
        'address' => fake()->address(),
        'status' => fake()->boolean(75),
    ]);
}

function createPost()
{
    return Post::query()->create([
        'title' => fake()->title(),
        'content' => fake()->text(),
        'status' => fake()->boolean(75),
    ]);
}
