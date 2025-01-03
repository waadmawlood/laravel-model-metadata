<?php

use Waad\Metadata\Tests\App\Models\Company;
use Waad\Metadata\Models\Metadata;

// Test to ensure a company can be created
it('can create a company', function () {
    $company = createCompany();

    // Check that the created company is an instance of Company
    expect($company)
        ->toBeInstanceOf(Company::class)
        ->and($company->name)->not->toBeEmpty() // Ensure the name is not empty
        ->and($company->address)->not->toBeEmpty() // Ensure the address is not empty
        ->and($company->status)->toBeBool(); // Ensure the status is a boolean
});

// Test to ensure metadata can be attached to a company
it('can attach metadata to company', function () {
    $company = createCompany();
    expect($company)->toBeInstanceOf(Company::class);

    // Create metadata for the company
    $metadata = $company->createMetadata([
        'language' => 'English',
        'is_visible' => true,
        'phone' => '',
        'slug' => null,
        'theme' => 'dark',
    ]);

    // Check that the created metadata is an instance of Metadata
    expect($metadata)->toBeInstanceOf(Metadata::class);
});

// Test to ensure company metadata can be updated
it('can update company metadata', function () {
    $company = createCompany();
    expect($company)->toBeInstanceOf(Company::class);

    // Create initial metadata
    $metadata = $company->createMetadata([
        'theme' => 'dark',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Update the metadata
    $status = $company->updateMetadata([
        'theme' => 'light',
    ]);
    expect($status)->toBeTrue(); // Ensure the update was successful
    expect($company->getMetadata())->toBe(['theme' => 'light']); // Verify the updated metadata
});

// Test to ensure existing company metadata can be updated
it('can update existing company metadata', function () {
    $company = createCompany();
    expect($company)->toBeInstanceOf(Company::class);

    // Create initial metadata
    $metadata = $company->createMetadata([
        'theme' => 'light',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Update the metadata
    $status = $company->updateMetadata([
        'theme' => 'dark',
        'language' => 'French',
    ]);
    expect($status)->toBeTrue(); // Ensure the update was successful

    // Verify the updated metadata
    $updatedMetadata = $company->getMetadata();
    expect($updatedMetadata)->toBeArray()
        ->and($updatedMetadata['theme'])->toBeString()->toBe('dark') // Check theme
        ->and($updatedMetadata['language'])->toBeString()->toBe('French'); // Check language
});

// Test to ensure company metadata can be deleted
it('can delete company metadata', function () {
    $company = createCompany();
    expect($company)->toBeInstanceOf(Company::class);

    // Create metadata for the company
    $metadata = $company->createMetadata([
        'theme' => 'dark',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Delete the metadata
    $status = $company->deleteMetadata();
    expect($status)->toBeTrue(); // Ensure the deletion was successful
    expect($company->getMetadata())->toBeNull(); // Verify that metadata is now null
});

// Test to ensure multiple types of metadata can be attached to a company
it('can attach multiple types of metadata to company', function () {
    $company = createCompany();
    expect($company)->toBeInstanceOf(Company::class);

    // Create multiple types of metadata for the company
    $metadata = $company->createMetadata([
        'language' => 'English',
        'is_visible' => true,
        'phone' => '',
        'slug' => null,
        'theme' => 'dark',
        'views' => 100,
        'rating' => 4.5,
    ]);

    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Retrieve the attached metadata
    $attachedMetadata = $company->getMetadata();

    // Verify the attached metadata
    expect($attachedMetadata)->toBeArray()
        ->and($attachedMetadata['language'])->toBeString()->toBe('English')
        ->and($attachedMetadata['is_visible'])->toBeTrue()
        ->and($attachedMetadata['phone'])->toBeString()->toBe('')
        ->and($attachedMetadata['slug'])->toBeNull()
        ->and($attachedMetadata['theme'])->toBeString()->toBe('dark')
        ->and($attachedMetadata['views'])->toBeInt()->toBe(100)
        ->and($attachedMetadata['rating'])->toBeFloat()->toBe(4.5);
});

// Test to ensure metadata can be retrieved as a collection
it('can retrieve metadata as a collection', function () {
    $company = createCompany();
    expect($company)->toBeInstanceOf(Company::class);

    // Create metadata for the company
    $metadata = $company->createMetadata([
        'theme' => 'dark',
        'language' => 'French',
    ]);
    expect($metadata)->toBeInstanceOf(Metadata::class);

    // Retrieve the metadata collection
    $metadataCollection = $company->getMetadataCollection();
    expect($metadataCollection)->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->and($metadataCollection->get('theme'))->toBeString()->toBe('dark') // Check theme
        ->and($metadataCollection->get('language'))->toBeString()->toBe('French'); // Check language
});