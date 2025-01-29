<?php

use Illuminate\Support\Collection;
use Waad\Metadata\Models\Metadata;
use Waad\Metadata\Tests\App\Models\Post;

beforeEach(function () {
    $this->post = createPost();
    expect($this->post)->toBeInstanceOf(Post::class);
});

// Test to ensure a post can be created
it('can create a post', function () {
    // Check that the created post is an instance of Post
    expect($this->post)
        ->toBeInstanceOf(Post::class)
        ->and($this->post->title)->not->toBeEmpty() // Ensure the title is not empty
        ->and($this->post->content)->not->toBeEmpty() // Ensure the content is not empty
        ->and($this->post->status)->toBeBool(); // Ensure the status is a boolean
});

// Test to ensure metadata can be attached to a post
it('can create metadata to post using createMetadata', function () {
    // Create metadata for the post
    $metadata = $this->post->createMetadata([
        'language' => 'English',
        'is_visible' => true,
        'author' => '',
        'slug' => null,
        'theme' => 'dark',
    ]);

    // Check that the created metadata is an instance of Metadata
    expect($metadata)->toBeInstanceOf(Metadata::class);
});

// Test to ensure multiple metadata records can be created for a post
it('can create multiple metadata records using createManyMetadata', function () {
    // Create multiple metadata records
    $metadataRecords = $this->post->createManyMetadata([
        [
            'language' => 'English',
            'theme' => 'light',
        ],
        [
            'language' => 'French',
            'theme' => 'dark',
        ],
        [
            'language' => 'Spanish',
            'theme' => 'auto',
        ],
    ]);
    expect($metadataRecords)->toBeCollection()->toHaveCount(3)->each->toBeInstanceOf(Metadata::class);

    // Verify the metadata values were stored correctly
    $storedMetadata = $this->post->getMetadata();
    expect($storedMetadata)->toBeArray()->toHaveCount(3);

    expect($storedMetadata[0])->toMatchArray(['language' => 'English', 'theme' => 'light']);
    expect($storedMetadata[1])->toMatchArray(['language' => 'French', 'theme' => 'dark']);
    expect($storedMetadata[2])->toMatchArray(['language' => 'Spanish', 'theme' => 'auto']);

    // testing as collection
    $metadataRecords = $this->post->createManyMetadata(collect([
        collect([
            'language' => 'English',
            'theme' => 'light',
        ]),
        collect([
            'language' => 'French',
            'theme' => 'dark',
        ]),
    ]));
    expect($metadataRecords)->toBeCollection()->toHaveCount(2)->each->toBeInstanceOf(Metadata::class);

    // Test to ensure nested metadata is not allowed
    expect($this->post->createManyMetadata([
        'language' => 'Arabic',
        'theme' => 'dark',
    ]))->toBeFalse();
    expect($this->post->createManyMetadata(collect([
        'language' => 'Arabic',
        'theme' => 'dark',
    ])))->toBeFalse();

    expect($this->post->createManyMetadata([]))->toBeFalse();
    expect($this->post->createManyMetadata([[]]))->toBeFalse();
    expect($this->post->createManyMetadata([null]))->toBeFalse();
    expect($this->post->createManyMetadata([collect([])]))->toBeFalse();
    expect($this->post->createManyMetadata([collect([''])]))->toBeFalse();
});

// Test metadata supports multiple data types (string, bool, null, int, float)
it('can add one metadata with multiple types', function () {
    // Create multiple types of metadata for the post
    $metadata = $this->post->createMetadata([
        'language' => 'English',
        'is_visible' => true,
        'author' => '',
        'slug' => null,
        'theme' => 'dark',
        'views' => 100,
        'rating' => 4.5,
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Retrieve the attached metadata
    $attachedMetadata = $this->post->getMetadata();

    // Verify the attached metadata
    expect($attachedMetadata)->toBeArray()
        ->and($attachedMetadata[0]['language'])->toBeString()->toBe('English')
        ->and($attachedMetadata[0]['is_visible'])->toBeTrue()
        ->and($attachedMetadata[0]['author'])->toBeString()->toBe('')
        ->and($attachedMetadata[0]['slug'])->toBeNull()
        ->and($attachedMetadata[0]['theme'])->toBeString()->toBe('dark')
        ->and($attachedMetadata[0]['views'])->toBeInt()->toBe(100)
        ->and($attachedMetadata[0]['rating'])->toBeFloat()->toBe(4.5);
});

// Test retrieving metadata by ID using getMetadataById
it('can get metadata by ID using getMetadataById', function () {
    // Create metadata record
    $metadata = $this->post->createMetadata([
        'language' => 'English',
        'theme' => 'light',
        'is_visible' => true,
        'views' => 100,
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Get metadata by ID without specifying keys
    $retrievedMetadata = $this->post->getMetadataById($metadata->id);

    // Verify all metadata was retrieved correctly
    expect($retrievedMetadata)->toBeArray()
        ->toMatchArray([
            'language' => 'English',
            'theme' => 'light',
            'is_visible' => true,
            'views' => 100,
        ]);

    // Get only specific keys from metadata
    $retrievedMetadata = $this->post->getMetadataById($metadata->id, ['language', 'theme']);

    // Verify only requested keys were retrieved
    expect($retrievedMetadata)->toBeArray()->toHaveCount(2)
        ->toMatchArray([
            'language' => 'English',
            'theme' => 'light',
        ]);

    // Test retrieving single key
    $singleKeyMetadata = $this->post->getMetadataById($metadata->id, 'views');
    expect($singleKeyMetadata)->toBeArray()->toHaveCount(1)->toMatchArray(['views' => 100]);
});

// Test adding multiple keys to metadata using addKeysMetadataById
it('can add multiple keys to metadata using addKeysMetadataById', function () {
    // Create initial metadata
    $metadata = $this->post->createMetadata([
        'language' => 'English',
        'theme' => 'light',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Add multiple keys to metadata
    $status = $this->post->addKeysMetadataById($metadata->id, [
        'is_visible' => true,
        'views' => 100,
    ]);
    expect($status)->toBeTrue();

    // Verify the metadata was updated correctly
    $updatedMetadata = $this->post->getMetadataById($metadata->id);
    expect($updatedMetadata)
        ->toMatchArray([
            'language' => 'English',
            'theme' => 'light',
            'is_visible' => true,
            'views' => 100,
        ]);
});

// Test adding a single key to metadata using addKeyMetadataById
it('can add single key to metadata using addKeyMetadataById', function () {
    // Create initial metadata
    $metadata = $this->post->createMetadata([
        'language' => 'English',
    ]);

    // Add single key to metadata
    $status = $this->post->addKeyMetadataById($metadata->id, 'is_visible', true);
    expect($status)->toBeTrue();

    // Verify the metadata was updated correctly
    $updatedMetadata = $this->post->getMetadataById($metadata->id);
    expect($updatedMetadata)
        ->toMatchArray([
            'language' => 'English',
            'is_visible' => true,
        ]);
});

// Test adding keys to metadata with invalid inputs
it('handles invalid inputs when adding metadata keys', function () {
    // Create initial metadata
    $metadata = $this->post->createMetadata([
        'language' => 'English',
    ]);

    expect($this->post->addKeysMetadataById($metadata->id, null))->toBeFalse();     // Test with null key
    expect($this->post->addKeyMetadataById($metadata->id, ''))->toBeFalse(); // Test with empty string key
    expect($this->post->addKeysMetadataById($metadata->id, []))->toBeFalse(); // Test with empty array

    // Verify original metadata remains unchanged
    $unchangedMetadata = $this->post->getMetadataById($metadata->id);
    expect($unchangedMetadata)->toMatchArray(['language' => 'English']);
});

// Test to ensure post metadata can be updated
it('can update post metadata using updateMetadataById', function () {
    // Create initial metadata
    $metadata = $this->post->createMetadata([
        'theme' => 'dark',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Update the metadata
    $status = $this->post->updateMetadataById($metadata->id, [
        'theme' => 'light',
    ]);
    expect($status)->toBeTrue();

    // Get updated metadata and verify changes
    $updatedMetadata = $this->post->getMetadataById($metadata->id);
    expect($updatedMetadata)->toMatchArray(['theme' => 'light']);
});

// Test updating multiple keys in metadata using updateKeysMetadataById
it('can update multiple keys in metadata using updateKeysMetadataById', function () {
    // Create initial metadata
    $metadata = $this->post->createMetadata([
        'language' => 'English',
        'theme' => 'light',
        'views' => 100,
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Update multiple keys in metadata
    $status = $this->post->updateKeysMetadataById($metadata->id, [
        'theme' => 'dark',
        'views' => 200,
    ]);
    expect($status)->toBeTrue();

    // Verify the metadata was updated correctly
    $updatedMetadata = $this->post->getMetadataById($metadata->id);
    expect($updatedMetadata)
        ->toMatchArray([
            'language' => 'English',
            'theme' => 'dark',
            'views' => 200,
        ]);
});

// Test updating a single key in metadata using updateKeyMetadataById
it('can update single key in metadata using updateKeyMetadataById', function () {
    // Create initial metadata
    $metadata = $this->post->createMetadata([
        'language' => 'English',
        'theme' => 'light',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Update single key in metadata
    $status = $this->post->updateKeyMetadataById($metadata->id, 'theme', 'dark');
    expect($status)->toBeTrue();

    // Verify the metadata was updated correctly
    $updatedMetadata = $this->post->getMetadataById($metadata->id);
    expect($updatedMetadata)
        ->toMatchArray([
            'language' => 'English',
            'theme' => 'dark',
        ]);
});

// Test to ensure post metadata can be synced
it('can sync post metadata using syncMetadata', function () {
    // Create initial metadata
    $this->post->createManyMetadata([
        [
            'language' => 'English',
            'theme' => 'light',
        ],
        [
            'language' => 'Arabic',
            'theme' => 'dark',
        ],
    ]);

    expect($this->post->getMetadata())->toBeArray()->toHaveCount(2);

    // Sync with new metadata
    $status = $this->post->syncMetadata([
        [
            'language' => 'Spanish',
            'theme' => 'auto',
        ],
    ]);
    expect($status)->toBeTrue();

    // Verify old metadata was deleted and new metadata was created
    $syncedMetadata = $this->post->getMetadata();
    expect($syncedMetadata)->toBeArray()->toHaveCount(1)
        ->and($syncedMetadata[0])
        ->toMatchArray([
            'language' => 'Spanish',
            'theme' => 'auto',
        ]);

    // testing as collection
    $status = $this->post->syncMetadata(collect([
        collect([
            'language' => 'Spanish',
            'theme' => 'auto',
        ]),
    ]));
    expect($status)->toBeTrue();

    // Test to ensure syncMetadata handles empty arrays
    expect($this->post->syncMetadata([]))->toBeTrue();
    expect($this->post->syncMetadata([[]]))->toBeFalse();
    expect($this->post->syncMetadata([null]))->toBeFalse();
    expect($this->post->syncMetadata(['']))->toBeFalse();
    expect($this->post->syncMetadata(collect([])))->toBeTrue();
    expect($this->post->syncMetadata([collect([])]))->toBeFalse();
    expect($this->post->syncMetadata(collect([collect([])])))->toBeFalse();
    expect($this->post->syncMetadata(collect([collect([''])])))->toBeFalse();
});

// Test to ensure post metadata can be deleted
it('can delete post metadata using deleteMetadataById and deleteMetadata', function () {
    // Create metadata for the post
    $metadata = $this->post->createMetadata([
        'theme' => 'dark',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Delete the metadata
    $status = $this->post->deleteMetadataById($metadata->id);
    expect($status)->toBeTrue(); // Ensure the deletion was successful
    expect($this->post->getMetadata())->toBeEmpty(); // Verify that metadata is now empty

    // Create multiple metadata records
    $this->post->createManyMetadata([
        [
            'theme' => 'light',
            'language' => 'English',
        ],
        [
            'theme' => 'dark',
            'language' => 'Spanish',
        ],
    ]);
    expect($this->post->getMetadata())->toHaveCount(2);

    // Delete all metadata
    $status = $this->post->deleteMetadata();
    expect($status)->toBeTrue();
    expect($this->post->getMetadata())->toBeEmpty();
});

// Test to ensure metadata content can be forgotten
it('can forget metadata content using forgetMetadataById, forgetKeysMetadataById, forgetKeyMetadataById', function () {
    // Create metadata for testing
    $metadata = $this->post->createMetadata([
        'theme' => 'dark',
        'language' => 'English',
        'notifications' => true,
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Test forgetMetadataById - sets metadata to null
    $status = $this->post->forgetMetadataById($metadata->id);
    expect($status)->toBeTrue();
    expect($this->post->getMetadataById($metadata->id))->toBeArray()->toBeEmpty();

    // Create new metadata for testing keys
    $metadata = $this->post->createMetadata([
        'theme' => 'light',
        'language' => 'Arabic',
        'notifications' => false,
    ]);

    // Test forgetKeysMetadataById - removes specific keys
    $status = $this->post->forgetKeysMetadataById($metadata->id, ['theme', 'notifications']);
    expect($status)->toBeTrue();
    expect($this->post->getMetadataById($metadata->id))->toBeArray()->toHaveCount(1)->toMatchArray(['language' => 'Arabic']);

    // Test forgetKeyMetadataById - removes single key
    $status = $this->post->forgetKeyMetadataById($metadata->id, 'language');
    expect($status)->toBeTrue();
    expect($this->post->getMetadataById($metadata->id))->toBeArray()->toBeEmpty();
    expect($this->post->setMetadataNameIdEnabled(false)->getMetadataById($metadata->id))->toBeArray()->toBeEmpty();
});

// Test to ensure metadata can be retrieved as a collection
it('can retrieve metadata as a collection using getMetadataCollection', function () {
    // Create metadata for the post
    $metadata = $this->post->createMetadata([
        'theme' => 'dark',
        'language' => 'Arabic',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Retrieve the metadata collection
    $metadataCollection = $this->post->getMetadataCollection();
    expect($metadataCollection)->toBeInstanceOf(Collection::class)
        ->and($metadataCollection->get(0)['theme'])->toBeString()->toBe('dark')
        ->and($metadataCollection->get(0)['language'])->toBeString()->toBe('Arabic');
});

it('can retrieve metadata by ID using getMetadataById', function () {
    // Create metadata for the post
    $metadata = $this->post->createMetadata([
        'language' => 'Arabic',
        'is_visible' => true,
    ]);

    // Retrieve metadata by ID and verify contents
    $retrievedMetadata = $this->post->getMetadataById($metadata->id);
    expect($retrievedMetadata)->toBeArray()->toHaveCount(2)
        ->and($retrievedMetadata)->toMatchArray([
            'language' => 'Arabic',
            'is_visible' => true,
        ]);
});

it('can get individual metadata value by ID and key using getKeyMetadataById', function () {
    // Create metadata with multiple fields
    $metadata = $this->post->createMetadata([
        'language' => 'Arabic',
        'is_visible' => true,
        'views' => 100,
        'rating' => 4.5,
        'theme' => null,
        'actors' => ['John Doe', 'Jane Smith'],
    ]);

    // Test retrieving different data types
    expect($this->post->getKeyMetadataById($metadata->id, 'language'))->toBeString()->toBe('Arabic');
    expect($this->post->getKeyMetadataById($metadata->id, 'is_visible'))->toBeBool()->toBeTrue();
    expect($this->post->getKeyMetadataById($metadata->id, 'views'))->toBeInt()->toBe(100);
    expect($this->post->getKeyMetadataById($metadata->id, 'rating'))->toBeFloat()->toBe(4.5);
    expect($this->post->getKeyMetadataById($metadata->id, 'theme'))->toBeNull();
    expect($this->post->getKeyMetadataById($metadata->id, 'actors'))->toBeArray()->toBe(['John Doe', 'Jane Smith']);

    // Test retrieving non-existent key
    expect($this->post->getKeyMetadataById($metadata->id, 'non_existent'))->toBeNull();
});

it('can search metadata using searchMetadataCollection, searchMetadata', function () {
    // Create metadata for the post
    $this->post->createMetadata([
        'language' => 'Arabic',
        'is_visible' => true,
    ]);
    $this->post->createMetadata([
        'language' => 'English',
        'is_visible' => false,
    ]);

    // Search for metadata by language to result collection
    $searchResults = $this->post->searchMetadataCollection('Arabic');
    expect($searchResults)->toBeInstanceOf(Collection::class)
        ->and($searchResults->count())->toBe(1)
        ->and($searchResults->first()['language'])->toBeString()->toBe('Arabic')
        ->and($searchResults->first()['is_visible'])->toBeTrue();

    // Search for metadata by language to result array
    $searchResults = $this->post->searchMetadata('Arabic');
    expect($searchResults)->toBeArray()->toHaveCount(1)
        ->and($searchResults[0]['language'])->toBeString()->toBe('Arabic')
        ->and($searchResults[0]['is_visible'])->toBeTrue();
});

// Test to ensure model has metadata using hasMetadata, hasMetadataById
it('can check if model has metadata using hasMetadata, hasMetadataById', function () {
    // check if model has metadata using hasMetadata
    $this->post->createMetadata([
        'language' => 'Arabic',
        'is_visible' => true,
    ]);
    expect($this->post->hasMetadata())->toBeTrue();

    // check if model has metadata using hasMetadataById
    $metadata = $this->post->getMetadata();
    expect($metadata)->toBeArray()->toHaveCount(1);
    expect($this->post->hasMetadataById($metadata[0]['id']))->toBeTrue();

    // check if model has filled metadata using hasFilledMetadataById
    expect($this->post->hasFilledMetadataById($metadata[0]['id']))->toBeTrue();
    $this->post->forgetMetadataById($metadata[0]['id']);
    expect($this->post->hasFilledMetadataById($metadata[0]['id']))->toBeFalse();
});
