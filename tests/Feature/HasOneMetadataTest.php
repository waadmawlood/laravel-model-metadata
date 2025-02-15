<?php

use Illuminate\Support\Collection;
use Waad\Metadata\Models\Metadata;
use Waad\Metadata\Tests\App\Models\Company;

beforeEach(function () {
    $this->company = createCompany();
    expect($this->company)->toBeInstanceOf(Company::class);
});

// Test to ensure a company can be created
it('can create a company', function () {
    // Check that the created company is an instance of Company
    expect($this->company)
        ->toBeInstanceOf(Company::class)
        ->and($this->company->name)->not->toBeEmpty() // Ensure the name is not empty
        ->and($this->company->address)->not->toBeEmpty() // Ensure the address is not empty
        ->and($this->company->status)->toBeBool(); // Ensure the status is a boolean
});

// Test to ensure metadata can be attached to a company using createMetadata
it('can create metadata to company using createMetadata', function () {
    // Create metadata for the company
    $metadata = $this->company->createMetadata([
        'language' => 'English',
        'is_visible' => true,
        'phone' => '',
        'slug' => null,
        'theme' => 'dark',
    ]);

    // Check that the created metadata is an instance of Metadata
    expect($metadata)->toBeInstanceOf(Metadata::class);
});

// Test to ensure metadata can be retrieved using getMetadata and getKeyMetadata
it('can retrieve metadata using getMetadata and getKeyMetadata', function () {
    // Create metadata for the company
    $this->company->createMetadata([
        'theme' => 'dark',
        'language' => 'English',
        'views' => 100,
        'settings' => ['notifications' => true],
    ]);

    // Test getMetadata with no parameters (get all metadata)
    $metadata = $this->company->getMetadata();
    expect($metadata)->toBeArray()
        ->and($metadata['theme'])->toBe('dark')
        ->and($metadata['language'])->toBe('English')
        ->and($metadata['views'])->toBe(100)
        ->and($metadata['settings'])->toBeArray()
        ->and($metadata['settings']['notifications'])->toBeTrue();

    // Test getMetadata with specific keys
    $partialMetadata = $this->company->getMetadata(['theme', 'language']);
    expect($partialMetadata)->toBeArray()
        ->and($partialMetadata)->toHaveCount(2)
        ->and($partialMetadata['theme'])->toBe('dark')
        ->and($partialMetadata['language'])->toBe('English');

    // Test getMetadata with specific key
    $partialMetadata = $this->company->getMetadata('theme');
    expect($partialMetadata)->toBeArray()
        ->and($partialMetadata)->toHaveCount(1)
        ->and($partialMetadata['theme'])->toBe('dark');

    // Test getKeyMetadata for individual values
    expect($this->company->getKeyMetadata('theme'))->toBe('dark')
        ->and($this->company->getKeyMetadata('views'))->toBe(100)
        ->and($this->company->getKeyMetadata('settings'))->toBeArray()
        ->and($this->company->getKeyMetadata('nonexistent'))->toBeNull();

    $this->company->syncMetadata([]);
    expect($this->company->getMetadata())->toBeArray()->toBeEmpty();
});

// Test to ensure metadata can be retrieved as a collection
it('can retrieve metadata as a collection using getMetadataCollection', function () {
    // Create metadata for the company
    $metadata = $this->company->createMetadata(['theme' => 'dark', 'language' => 'French']);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Retrieve the metadata collection
    $metadataCollection = $this->company->getMetadataCollection();
    expect($metadataCollection)->toBeInstanceOf(Collection::class)
        ->and($metadataCollection->get('theme'))->toBeString()->toBe('dark')
        ->and($metadataCollection->get('language'))->toBeString()->toBe('French');

    // Test getMetadataCollection with specific keys
    $partialMetadataCollection = $this->company->getMetadataCollection(['theme']);
    expect($partialMetadataCollection)->toBeInstanceOf(Collection::class)
        ->and($partialMetadataCollection->get('theme'))->toBeString()->toBe('dark');

    // Test getMetadataCollection with specific key
    $partialMetadataCollection = $this->company->getMetadataCollection('theme');
    expect($partialMetadataCollection)->toBeInstanceOf(Collection::class)
        ->and($partialMetadataCollection->get('theme'))->toBeString()->toBe('dark');
});

// Test to ensure multiple types of metadata can be attached to a company
it('can create multiple types of metadata to company', function () {
    // Create multiple types of metadata for the company
    $metadata = $this->company->createMetadata([
        'language' => 'English',
        'is_visible' => true,
        'phone' => '',
        'slug' => null,
        'theme' => 'dark',
        'views' => 100,
        'rating' => 4.5,
        'sports' => ['football', 'basketball'],
    ]);

    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Retrieve the attached metadata
    $attachedMetadata = $this->company->getMetadata();

    // Verify the attached metadata
    expect($attachedMetadata)->toBeArray()
        ->and($attachedMetadata['language'])->toBeString()->toBe('English')
        ->and($attachedMetadata['is_visible'])->toBeTrue()
        ->and($attachedMetadata['phone'])->toBeString()->toBe('')
        ->and($attachedMetadata['slug'])->toBeNull()
        ->and($attachedMetadata['theme'])->toBeString()->toBe('dark')
        ->and($attachedMetadata['views'])->toBeInt()->toBe(100)
        ->and($attachedMetadata['rating'])->toBeFloat()->toBe(4.5)
        ->and($attachedMetadata['sports'])->toBeArray()->not->toBeEmpty();
});

// Test to ensure metadata can be added using addKeysMetadata
it('can add multiple values by keys to metadata field using addKeysMetadata', function () {
    // Create initial metadata
    $this->company->createMetadata(['theme' => 'dark']);

    // Add multiple metadata keys
    $status = $this->company->addKeysMetadata([
        'language' => 'English',
        'is_visible' => true,
    ]);
    expect($status)->toBeTrue();

    // Verify the combined metadata
    $updatedMetadata = $this->company->getMetadata();
    expect($updatedMetadata)->toBeArray()
        ->and($updatedMetadata['theme'])->toBe('dark')
        ->and($updatedMetadata['language'])->toBe('English')
        ->and($updatedMetadata['is_visible'])->toBeTrue();
});

// Test to ensure metadata can be added using addKeyMetadata
it('can add value by one key to metadata field using addKeyMetadata', function () {
    // Create initial metadata
    $this->company->createMetadata(['theme' => 'dark']);

    // Add single metadata key
    $status = $this->company->addKeyMetadata('language', 'French');
    expect($status)->toBeTrue();

    // Verify the combined metadata
    $updatedMetadata = $this->company->getMetadata();
    expect($updatedMetadata)->toBeArray()
        ->and($updatedMetadata['theme'])->toBe('dark')
        ->and($updatedMetadata['language'])->toBe('French');

    // Test adding another key
    $status = $this->company->addKeyMetadata('is_visible', true);
    expect($status)->toBeTrue();

    // Verify all metadata
    $finalMetadata = $this->company->getMetadata();
    expect($finalMetadata)->toBeArray()
        ->and($finalMetadata['theme'])->toBe('dark')
        ->and($finalMetadata['language'])->toBe('French')
        ->and($finalMetadata['is_visible'])->toBeTrue();
});

// Test to ensure company metadata can be updated using updateMetadata
it('can update company metadata using updateMetadata', function () {
    // Create initial metadata
    $this->company->createMetadata(['theme' => 'dark']);

    // Update the metadata
    $status = $this->company->updateMetadata(['theme' => 'light']);
    expect($status)->toBeTrue(); // Ensure the update was successful
    expect($this->company->getMetadata())->toBe(['theme' => 'light']); // Verify the updated metadata

    // Update the metadata
    $status = $this->company->updateMetadata(['theme' => 'dark', 'language' => 'French']);
    expect($status)->toBeTrue(); // Ensure the update was successful

    // Verify the updated metadata
    $updatedMetadata = $this->company->getMetadata();
    expect($updatedMetadata)->toBeArray()
        ->and($updatedMetadata['theme'])->toBeString()->toBe('dark') // Check theme
        ->and($updatedMetadata['language'])->toBeString()->toBe('French'); // Check language
});

// Test to ensure metadata can be updated using updateKeysMetadata
it('can update values by multiple keys in metadata field using updateKeysMetadata', function () {
    // Create initial metadata
    $this->company->createMetadata(['theme' => 'dark', 'language' => 'English', 'is_visible' => true]);

    // Update multiple metadata keys
    $status = $this->company->updateKeysMetadata(['theme' => 'light', 'language' => 'Arabic']);
    expect($status)->toBeTrue();

    // Verify the updated metadata
    $updatedMetadata = $this->company->getMetadata();
    expect($updatedMetadata)->toBeArray()
        ->and($updatedMetadata['theme'])->toBe('light')
        ->and($updatedMetadata['language'])->toBe('Arabic')
        ->and($updatedMetadata['is_visible'])->toBeTrue();

    // Test updating with array
    $status = $this->company->updateKeysMetadata(['views' => 100, 'rating' => 4.5]);
    expect($status)->toBeTrue();

    // Verify final metadata
    $finalMetadata = $this->company->getMetadata();
    expect($finalMetadata)->toBeArray()
        ->and($finalMetadata['theme'])->toBe('light')
        ->and($finalMetadata['language'])->toBe('Arabic')
        ->and($finalMetadata['is_visible'])->toBeTrue()
        ->and($finalMetadata['views'])->toBe(100)
        ->and($finalMetadata['rating'])->toBe(4.5);
});

// Test to ensure metadata can be updated using updateKeyMetadata
it('can update value by one key in metadata field using updateKeyMetadata', function () {
    // Create initial metadata
    $this->company->createMetadata(['theme' => 'dark', 'language' => 'English']);

    // Update single metadata key
    $status = $this->company->updateKeyMetadata('theme', 'light');
    expect($status)->toBeTrue();

    // Verify the updated metadata
    $updatedMetadata = $this->company->getMetadata();
    expect($updatedMetadata)->toBeArray()
        ->and($updatedMetadata['theme'])->toBe('light')
        ->and($updatedMetadata['language'])->toBe('English');

    // Test updating with different type
    $status = $this->company->updateKeyMetadata('language', 'Arabic');
    expect($status)->toBeTrue();

    // Verify final metadata
    $finalMetadata = $this->company->getMetadata();
    expect($finalMetadata)->toBeArray()
        ->and($finalMetadata['theme'])->toBe('light')
        ->and($finalMetadata['language'])->toBe('Arabic');
});

// Test to ensure company metadata can be deleted
it('can delete company metadata using deleteMetadata', function () {
    // Create metadata for the company
    $this->company->createMetadata(['theme' => 'dark']);

    // Delete the metadata
    $status = $this->company->deleteMetadata();
    expect($status)->toBeTrue(); // Ensure the deletion was successful
    expect($this->company->getMetadata())->toBeArray()->toBeEmpty(); // Verify that metadata is now empty array
});

// Test to ensure metadata can be cleared using forgetMetadata, forgetKeysMetadata and forgetKeysMetadata
it('can clear content of metadata using forgetMetadata, forgetKeysMetadata, forgetKeysMetadata', function () {
    // Create initial metadata
    $this->company->createMetadata([
        'theme' => 'dark',
        'language' => 'English',
        'views' => 100,
        'settings' => ['notifications' => true],
    ]);

    // Test forgetKeysMetadata
    $status = $this->company->forgetKeysMetadata('theme');
    expect($status)->toBeTrue();

    $metadata = $this->company->getMetadata();
    expect($metadata)->toBeArray()
        ->and($metadata)->not->toHaveKey('theme')
        ->and($metadata['language'])->toBe('English')
        ->and($metadata['views'])->toBe(100);

    // Test forgetKeysMetadata with array
    $status = $this->company->forgetKeysMetadata(['language', 'views']);
    expect($status)->toBeTrue();

    $metadata = $this->company->getMetadata();
    expect($metadata)->toBeArray()
        ->and($metadata)->not->toHaveKey('language')
        ->and($metadata)->not->toHaveKey('views')
        ->and($metadata['settings'])->toBeArray();

    // Test forgetMetadata to empty all metadata
    $status = $this->company->forgetMetadata();
    expect($status)->toBeTrue();

    $metadata = $this->company->getMetadata();
    expect($metadata)->toBeArray()->toBeEmpty();
});

// Test to ensure metadata is exists using hasMetadata, hasFilledMetadata
it('can check metadata is exists using hasMetadata, hasFilledMetadata', function () {
    // Create metadata for the company
    $this->company->createMetadata(['theme' => 'dark', 'language' => 'French']);

    // Check if metadata exists
    $status = $this->company->hasMetadata();
    expect($status)->toBeBool()->toBeTrue();

    // Check if metadata is filled
    $status = $this->company->hasFilledMetadata();
    expect($status)->toBeBool()->toBeTrue();

    // Check if metadata is not filled
    $this->company->forgetMetadata();
    $status = $this->company->hasFilledMetadata();
    expect($status)->toBeBool()->toBeFalse();
});

// Test to ensure specific metadata key existence can be checked
it('can check if specific metadata key exists using hasAllKeysMetadata, hasKeyMetadata, hasAnyKeysMetadata', function () {
    // Create metadata for the company
    $this->company->createMetadata(['theme' => 'dark', 'views' => 100]);

    // Using hasKeyMetadata
    expect($this->company->hasKeyMetadata('theme'))->toBeTrue()
        ->and($this->company->hasKeyMetadata('views'))->toBeTrue(); // Check existing keys
    expect($this->company->hasKeyMetadata('invalid_key'))->toBeFalse()
        ->and($this->company->hasKeyMetadata(''))->toBeFalse()
        ->and($this->company->hasKeyMetadata(null))->toBeFalse(); // Check non-existing keys

    // Using hasAllKeysMetadata
    expect($this->company->hasAllKeysMetadata(['theme', 'views']))->toBeTrue(); // Check existing keys
    expect($this->company->hasAllKeysMetadata(['theme', 'invalid_key']))->toBeFalse(); // Check non-existing keys

    // Using hasAnyKeysMetadata
    expect($this->company->hasAnyKeysMetadata(['theme', 'views']))->toBeTrue(); // Check existing keys
    expect($this->company->hasAnyKeysMetadata(['theme', 'invalid_key']))->toBeTrue(); // Check one existing key
    expect($this->company->hasAnyKeysMetadata(['invalid_key1', 'invalid_key2']))->toBeFalse(); // Check non-existing keys
});
