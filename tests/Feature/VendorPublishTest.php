<?php

use function Pest\Laravel\artisan;

it('publishing config file with artisan command works', function () {
    $configPath = config_path('model-metadata.php');

    // Ensure config file does not exist before publishing
    if (file_exists($configPath)) {
        unlink($configPath);
    }
    expect(file_exists($configPath))->toBeFalse();

    // Run the vendor:publish command for config
    artisan('vendor:publish', [
        '--tag' => 'metadata-config',
    ])->assertSuccessful();

    // Assert the config file now exists
    expect(file_exists($configPath))->toBeTrue();

    // Clean up
    if (file_exists($configPath)) {
        unlink($configPath);
    }
});

it('publishing migration file with artisan command works', function () {
    $migrationDir = database_path('migrations');
    $migrationFiles = glob($migrationDir.'/*_create_model_meta_data_table.php');

    // Remove any existing migration files for a clean test
    foreach ($migrationFiles as $file) {
        unlink($file);
    }
    expect(glob($migrationDir.'/*_create_model_meta_data_table.php'))->toBeEmpty();

    // Run the vendor:publish command for migrations
    artisan('vendor:publish', [
        '--tag' => 'metadata-migrations',
    ])->assertSuccessful();

    // Assert that at least one migration file now exists
    $migrationFiles = glob($migrationDir.'/*_create_model_meta_data_table.php');
    expect($migrationFiles)->not->toBeEmpty();

    // Clean up
    foreach ($migrationFiles as $file) {
        unlink($file);
    }
});
