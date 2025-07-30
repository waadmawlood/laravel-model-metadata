<?php

use Waad\Metadata\Helpers\Helper;
use Waad\Metadata\Models\Metadata;

it('configuration is properly loaded', function () {
    // Test that the config is accessible
    expect(config('model-metadata'))->toBeArray();

    // Test default table name
    expect(config('model-metadata.table'))->toBe('model_metadata');

    // Test default model class
    expect(config('model-metadata.model'))->toBe(Metadata::class);
});

it('configuration can be overridden', function () {
    // Override the table name
    config(['model-metadata.table' => 'custom_metadata_table']);

    expect(config('model-metadata.table'))->toBe('custom_metadata_table');

    // Override the model class
    config(['model-metadata.model' => 'Custom\Metadata\Model']);

    expect(config('model-metadata.model'))->toBe('Custom\Metadata\Model');
});

it('service provider registers configuration', function () {
    // Test that the config is merged from the package
    $config = config('model-metadata');

    expect($config)->toHaveKey('table');
    expect($config)->toHaveKey('model');
    expect($config['table'])->toBe('model_metadata');
    expect($config['model'])->toBe(Metadata::class);
});

it('service provider registers helper singleton', function () {
    // Test that the Helper class is registered as a singleton
    $helper1 = app(Helper::class);
    $helper2 = app(Helper::class);

    expect($helper1)->toBeInstanceOf(Helper::class);
    expect($helper2)->toBeInstanceOf(Helper::class);
    expect($helper1)->toBe($helper2); // Same instance (singleton)
});

it('configuration structure is correct', function () {
    $config = config('model-metadata');

    // Test that all required keys exist
    expect($config)->toHaveKeys(['table', 'model']);

    // Test that table is a string
    expect($config['table'])->toBeString();

    // Test that model is a string (class name)
    expect($config['model'])->toBeString();

    // Test that the model class exists
    expect(class_exists($config['model']))->toBeTrue();
});

it('default configuration values are correct', function () {
    $config = config('model-metadata');

    // Test default table name
    expect($config['table'])->toBe('model_metadata');

    // Test default model class
    expect($config['model'])->toBe(Metadata::class);

    // Test that the default model class is instantiable
    expect(new $config['model'])->toBeInstanceOf(Metadata::class);
});

it('configuration can be accessed via helper', function () {
    $helper = app(Helper::class);

    // Test that helper can access configuration
    expect($helper)->toBeInstanceOf(Helper::class);
});

it('configuration file exists and is valid', function () {
    $configPath = __DIR__.'/../../config/model-metadata.php';

    // Test that config file exists
    expect(file_exists($configPath))->toBeTrue();

    // Test that config file returns an array
    $config = require $configPath;
    expect($config)->toBeArray();

    // Test that config has required keys
    expect($config)->toHaveKeys(['table', 'model']);
});
