<?php

namespace Waad\Metadata\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Waad\Metadata\Providers\MetadataServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            MetadataServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/App/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
    }
}
