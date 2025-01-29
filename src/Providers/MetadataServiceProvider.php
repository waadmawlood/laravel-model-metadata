<?php

namespace Waad\Metadata\Providers;

use Illuminate\Support\ServiceProvider;
use Waad\Metadata\Helpers\Helper;

class MetadataServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Helper::class);
    }

    public function boot()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../../migrations/1_create_model_meta_data_table.php' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_model_meta_data_table.php'),
        ], 'metadata-migrations');
    }
}
