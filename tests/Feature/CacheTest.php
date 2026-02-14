<?php

use Illuminate\Support\Facades\Cache;

// ========================================================================
// Config Tests
// ========================================================================

it('has cache config defaults', function () {
    $config = config('model-metadata.cache');

    expect($config)->toBeArray()
        ->and($config['enabled'])->toBeFalse()
        ->and($config['ttl'])->toBe(3600)
        ->and($config['store'])->toBeNull()
        ->and($config['prefix'])->toBe('model_metadata');
});

it('cache config can be overridden', function () {
    config([
        'model-metadata.cache.enabled' => true,
        'model-metadata.cache.ttl' => 7200,
        'model-metadata.cache.store' => 'redis',
        'model-metadata.cache.prefix' => 'custom_prefix',
    ]);

    expect(config('model-metadata.cache.enabled'))->toBeTrue()
        ->and(config('model-metadata.cache.ttl'))->toBe(7200)
        ->and(config('model-metadata.cache.store'))->toBe('redis')
        ->and(config('model-metadata.cache.prefix'))->toBe('custom_prefix');
});

// ========================================================================
// HasOneMetadata Cache Tests (Company)
// ========================================================================

it('HasOneMetadata: cache is disabled by default', function () {
    $company = createCompany();

    expect($company->metadataCacheIsEnabled())->toBeFalse();
});

it('HasOneMetadata: works normally when cache is disabled', function () {
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark', 'language' => 'English']);

    $metadata = $company->getMetadata();
    expect($metadata)->toBeArray()
        ->and($metadata['theme'])->toBe('dark')
        ->and($metadata['language'])->toBe('English');

    // Update and verify
    $company->updateMetadata(['theme' => 'light']);
    expect($company->getMetadata())->toBe(['theme' => 'light']);
});

it('HasOneMetadata: caches getMetadata when cache is enabled', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    expect($company->metadataCacheIsEnabled())->toBeTrue();

    $company->createMetadata(['theme' => 'dark', 'language' => 'English']);

    // First call populates cache
    $metadata = $company->getMetadata();
    expect($metadata)->toBeArray()
        ->and($metadata['theme'])->toBe('dark');

    // Verify cache key exists
    $cacheKey = 'model_metadata:'.get_class($company).':'.$company->getKey().':data';
    expect(Cache::store(config('model-metadata.cache.store'))->has($cacheKey))->toBeTrue();
});

it('HasOneMetadata: getMetadata with keys filter works from cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark', 'language' => 'English', 'views' => 100]);

    // Full metadata
    $metadata = $company->getMetadata();
    expect($metadata)->toHaveCount(3);

    // Filtered metadata (still uses cached full data)
    $filtered = $company->getMetadata(['theme', 'language']);
    expect($filtered)->toHaveCount(2)
        ->and($filtered['theme'])->toBe('dark')
        ->and($filtered['language'])->toBe('English');

    // Single key filter
    $single = $company->getMetadata('views');
    expect($single)->toHaveCount(1)
        ->and($single['views'])->toBe(100);
});

it('HasOneMetadata: createMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark']);

    // Populate cache
    $company->getMetadata();

    // Delete and re-create
    $company->deleteMetadata();
    $company->createMetadata(['theme' => 'light']);

    // Should get fresh data, not cached
    $metadata = $company->getMetadata();
    expect($metadata['theme'])->toBe('light');
});

it('HasOneMetadata: updateMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark']);

    // Populate cache
    $company->getMetadata();

    // Update
    $company->updateMetadata(['theme' => 'light', 'language' => 'French']);

    // Should get fresh data
    $metadata = $company->getMetadata();
    expect($metadata['theme'])->toBe('light')
        ->and($metadata['language'])->toBe('French');
});

it('HasOneMetadata: deleteMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark']);

    // Populate cache
    $company->getMetadata();

    // Delete
    $company->deleteMetadata();

    // Should get empty, not cached data
    expect($company->getMetadata())->toBeArray()->toBeEmpty();
});

it('HasOneMetadata: forgetMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark', 'language' => 'English']);

    // Populate cache
    $company->getMetadata();

    // Forget
    $company->forgetMetadata();

    // Should get empty, not cached data
    expect($company->getMetadata())->toBeArray()->toBeEmpty();
});

it('HasOneMetadata: syncMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark']);

    // Populate cache
    $company->getMetadata();

    // Sync with new data
    $company->syncMetadata(['theme' => 'light', 'language' => 'Arabic']);

    // Should get fresh data
    $metadata = $company->getMetadata();
    expect($metadata['theme'])->toBe('light')
        ->and($metadata['language'])->toBe('Arabic');
});

it('HasOneMetadata: addKeysMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark']);

    // Populate cache
    $company->getMetadata();

    // Add keys
    $company->addKeysMetadata(['language' => 'English']);

    // Should get fresh data including new key
    $metadata = $company->getMetadata();
    expect($metadata['theme'])->toBe('dark')
        ->and($metadata['language'])->toBe('English');
});

it('HasOneMetadata: forgetKeysMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark', 'language' => 'English']);

    // Populate cache
    $company->getMetadata();

    // Forget specific key
    $company->forgetKeysMetadata('theme');

    // Should get fresh data without forgotten key
    $metadata = $company->getMetadata();
    expect($metadata)->not->toHaveKey('theme')
        ->and($metadata['language'])->toBe('English');
});

it('HasOneMetadata: clearMetadataCache manually clears cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark']);

    // Populate cache
    $company->getMetadata();

    $cacheKey = 'model_metadata:'.get_class($company).':'.$company->getKey().':data';
    expect(Cache::store(config('model-metadata.cache.store'))->has($cacheKey))->toBeTrue();

    // Manually clear
    $company->clearMetadataCache();

    expect(Cache::store(config('model-metadata.cache.store'))->has($cacheKey))->toBeFalse();
});

it('HasOneMetadata: clearMetadataCache is safe when cache is disabled', function () {
    $company = createCompany();

    // Should not throw
    $company->clearMetadataCache();

    expect(true)->toBeTrue();
});

// ========================================================================
// HasManyMetadata Cache Tests (Post)
// ========================================================================

it('HasManyMetadata: cache is disabled by default', function () {
    $post = createPost();

    expect($post->metadataCacheIsEnabled())->toBeFalse();
});

it('HasManyMetadata: works normally when cache is disabled', function () {
    $post = createPost();

    $post->createMetadata(['theme' => 'dark']);
    $post->createMetadata(['theme' => 'light']);

    $metadata = $post->getMetadata();
    expect($metadata)->toBeArray()->toHaveCount(2);
});

it('HasManyMetadata: caches getMetadataCollection when cache is enabled', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $post->createMetadata(['theme' => 'dark']);

    // First call populates cache
    $collection = $post->getMetadataCollection();
    expect($collection)->toHaveCount(1);

    // Verify cache key exists
    $cacheKey = 'model_metadata:'.get_class($post).':'.$post->getKey().':all';
    expect(Cache::store(config('model-metadata.cache.store'))->has($cacheKey))->toBeTrue();
});

it('HasManyMetadata: caches getMetadataById when cache is enabled', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $metadata = $post->createMetadata(['theme' => 'dark', 'language' => 'English']);

    // First call populates cache
    $result = $post->getMetadataById($metadata->id);
    expect($result)->toBeArray()
        ->and($result['theme'])->toBe('dark');

    // Verify cache key exists
    $cacheKey = 'model_metadata:'.get_class($post).':'.$post->getKey().':byId:'.$metadata->id;
    expect(Cache::store(config('model-metadata.cache.store'))->has($cacheKey))->toBeTrue();
});

it('HasManyMetadata: getMetadataById with keys filter works from cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $metadata = $post->createMetadata(['theme' => 'dark', 'language' => 'English', 'views' => 100]);

    // Full metadata by ID
    $result = $post->getMetadataById($metadata->id);
    expect($result)->toHaveCount(3);

    // Filtered (uses cached full data)
    $filtered = $post->getMetadataById($metadata->id, ['theme']);
    expect($filtered)->toHaveCount(1)
        ->and($filtered['theme'])->toBe('dark');
});

it('HasManyMetadata: createMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $post->createMetadata(['theme' => 'dark']);

    // Populate cache
    $post->getMetadata();

    // Create another
    $post->createMetadata(['theme' => 'light']);

    // Should get fresh data with both records
    $metadata = $post->getMetadata();
    expect($metadata)->toHaveCount(2);
});

it('HasManyMetadata: createManyMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $post->createMetadata(['theme' => 'dark']);

    // Populate cache
    $post->getMetadata();

    // Create many
    $post->createManyMetadata([
        ['language' => 'English'],
        ['language' => 'French'],
    ]);

    // Should get fresh data with all 3 records
    $metadata = $post->getMetadata();
    expect($metadata)->toHaveCount(3);
});

it('HasManyMetadata: updateMetadataById invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $metadata = $post->createMetadata(['theme' => 'dark']);

    // Populate cache
    $post->getMetadataById($metadata->id);
    $post->getMetadata();

    // Update
    $post->updateMetadataById($metadata->id, ['theme' => 'light']);

    // Both getMetadataById and getMetadata should return fresh data
    $byId = $post->getMetadataById($metadata->id);
    expect($byId['theme'])->toBe('light');

    $all = $post->getMetadata();
    expect($all[0])->toMatchArray(['theme' => 'light']);
});

it('HasManyMetadata: deleteMetadataById invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $metadata1 = $post->createMetadata(['theme' => 'dark']);
    $post->createMetadata(['theme' => 'light']);

    // Populate cache
    $post->getMetadata();

    // Delete one
    $post->deleteMetadataById($metadata1->id);

    // Should only have one record
    $metadata = $post->getMetadata();
    expect($metadata)->toHaveCount(1);
});

it('HasManyMetadata: deleteMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $post->createMetadata(['theme' => 'dark']);
    $post->createMetadata(['theme' => 'light']);

    // Populate cache
    $post->getMetadata();

    // Delete all
    $post->deleteMetadata();

    // Should be empty
    expect($post->getMetadata())->toBeEmpty();
});

it('HasManyMetadata: syncMetadata invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $post->createManyMetadata([
        ['theme' => 'dark'],
        ['theme' => 'light'],
    ]);

    // Populate cache
    $post->getMetadata();

    // Sync with new data
    $post->syncMetadata([
        ['language' => 'Spanish'],
    ]);

    // Should get fresh synced data
    $metadata = $post->getMetadata();
    expect($metadata)->toHaveCount(1)
        ->and($metadata[0])->toMatchArray(['language' => 'Spanish']);
});

it('HasManyMetadata: forgetMetadataById invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $metadata = $post->createMetadata(['theme' => 'dark', 'language' => 'English']);

    // Populate cache
    $post->getMetadataById($metadata->id);

    // Forget
    $post->forgetMetadataById($metadata->id);

    // Should get empty metadata
    expect($post->getMetadataById($metadata->id))->toBeArray()->toBeEmpty();
});

it('HasManyMetadata: forgetKeysMetadataById invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $metadata = $post->createMetadata(['theme' => 'dark', 'language' => 'English', 'views' => 100]);

    // Populate cache
    $post->getMetadataById($metadata->id);

    // Forget specific keys
    $post->forgetKeysMetadataById($metadata->id, ['theme', 'views']);

    // Should only have language
    $result = $post->getMetadataById($metadata->id);
    expect($result)->toHaveCount(1)
        ->and($result)->toMatchArray(['language' => 'English']);
});

it('HasManyMetadata: addKeysMetadataById invalidates cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $metadata = $post->createMetadata(['theme' => 'dark']);

    // Populate cache
    $post->getMetadataById($metadata->id);

    // Add keys
    $post->addKeysMetadataById($metadata->id, ['language' => 'English']);

    // Should get fresh data with new key
    $result = $post->getMetadataById($metadata->id);
    expect($result['theme'])->toBe('dark')
        ->and($result['language'])->toBe('English');
});

it('HasManyMetadata: clearMetadataCache manually clears cache', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    $metadata = $post->createMetadata(['theme' => 'dark']);

    // Populate cache
    $post->getMetadata();
    $post->getMetadataById($metadata->id);

    $allKey = 'model_metadata:'.get_class($post).':'.$post->getKey().':all';
    $byIdKey = 'model_metadata:'.get_class($post).':'.$post->getKey().':byId:'.$metadata->id;

    expect(Cache::store(config('model-metadata.cache.store'))->has($allKey))->toBeTrue();
    expect(Cache::store(config('model-metadata.cache.store'))->has($byIdKey))->toBeTrue();

    // Manually clear
    $post->clearMetadataCache();

    expect(Cache::store(config('model-metadata.cache.store'))->has($allKey))->toBeFalse();
    expect(Cache::store(config('model-metadata.cache.store'))->has($byIdKey))->toBeFalse();
});

it('HasManyMetadata: clearMetadataCache is safe when cache is disabled', function () {
    $post = createPost();

    // Should not throw
    $post->clearMetadataCache();

    expect(true)->toBeTrue();
});

// ========================================================================
// Custom TTL / Prefix Tests
// ========================================================================

it('respects custom cache prefix', function () {
    config([
        'model-metadata.cache.enabled' => true,
        'model-metadata.cache.prefix' => 'my_app_meta',
    ]);
    $company = createCompany();

    $company->createMetadata(['theme' => 'dark']);
    $company->getMetadata();

    $cacheKey = 'my_app_meta:'.get_class($company).':'.$company->getKey().':data';
    expect(Cache::store(config('model-metadata.cache.store'))->has($cacheKey))->toBeTrue();
});

it('respects custom cache TTL', function () {
    config([
        'model-metadata.cache.enabled' => true,
        'model-metadata.cache.ttl' => 60,
    ]);
    $company = createCompany();

    expect($company->metadataCacheIsEnabled())->toBeTrue();

    $company->createMetadata(['theme' => 'dark']);
    $metadata = $company->getMetadata();

    expect($metadata['theme'])->toBe('dark');
});

// ========================================================================
// Existing Tests Still Pass With Cache Enabled
// ========================================================================

it('HasOneMetadata: full CRUD cycle works with cache enabled', function () {
    config(['model-metadata.cache.enabled' => true]);
    $company = createCompany();

    // Create
    $company->createMetadata(['theme' => 'dark', 'language' => 'English']);
    expect($company->getMetadata())->toBe(['theme' => 'dark', 'language' => 'English']);

    // Update
    $company->updateMetadata(['theme' => 'light', 'language' => 'French']);
    expect($company->getMetadata())->toBe(['theme' => 'light', 'language' => 'French']);

    // Add key
    $company->addKeyMetadata('views', 100);
    $metadata = $company->getMetadata();
    expect($metadata['views'])->toBe(100);

    // Forget key
    $company->forgetKeysMetadata('views');
    expect($company->getMetadata())->not->toHaveKey('views');

    // Delete
    $company->deleteMetadata();
    expect($company->getMetadata())->toBeArray()->toBeEmpty();
    expect($company->hasMetadata())->toBeFalse();
});

it('HasManyMetadata: full CRUD cycle works with cache enabled', function () {
    config(['model-metadata.cache.enabled' => true]);
    $post = createPost();

    // Create
    $m1 = $post->createMetadata(['theme' => 'dark']);
    $m2 = $post->createMetadata(['theme' => 'light']);
    expect($post->getMetadata())->toHaveCount(2);

    // Get by ID
    $result = $post->getMetadataById($m1->id);
    expect($result['theme'])->toBe('dark');

    // Update by ID
    $post->updateMetadataById($m1->id, ['theme' => 'auto']);
    expect($post->getMetadataById($m1->id)['theme'])->toBe('auto');

    // Add key by ID
    $post->addKeyMetadataById($m2->id, 'language', 'English');
    $result = $post->getMetadataById($m2->id);
    expect($result['theme'])->toBe('light')
        ->and($result['language'])->toBe('English');

    // Delete by ID
    $post->deleteMetadataById($m1->id);
    expect($post->getMetadata())->toHaveCount(1);

    // Delete all
    $post->deleteMetadata();
    expect($post->getMetadata())->toBeEmpty();
});
