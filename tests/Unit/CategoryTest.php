<?php
namespace Tests\Unit; // Correct namespace for unit tests

use Illuminate\Foundation\Testing\RefreshDatabase;

// Good to use even for some unit tests if they touch DB indirectly

uses(RefreshDatabase::class); // Or remove if purely unit and no DB interaction expected from model events

// it('automatically generates a slug when a category is created if slug is not provided', function () {
//     $category = Category::create(['name' => 'My Awesome Category']);
//     expect($category->slug)->toBe('my-awesome-category');
// });

// it('uses the provided slug if one is given during creation', function () {
//     $category = Category::create(['name' => 'My Awesome Category', 'slug' => 'custom-slug']);
//     expect($category->slug)->toBe('custom-slug');
// });

// it('updates slug when name changes and slug is not explicitly set during update', function () {
//     $category = Category::create(['name' => 'Old Name']);
//     expect($category->slug)->toBe('old-name');

//     $category->update(['name' => 'New Name']);
//     expect($category->fresh()->slug)->toBe('new-name'); // fresh() to get latest from DB if events run
// });

// it('does not update slug if slug is explicitly provided during update even if name changes', function () {
//     $category = Category::create(['name' => 'Original Name']);
//     expect($category->slug)->toBe('original-name');

//     $category->update(['name' => 'Changed Name', 'slug' => 'kept-original-slug']);
//     expect($category->fresh()->slug)->toBe('kept-original-slug');
//     expect($category->fresh()->name)->toBe('Changed Name');
// });

// it('does not change slug on update if name does not change and slug not provided', function () {
//     $category = Category::create(['name' => 'Stable Name', 'slug' => 'stable-slug']);
//     $category->update(['description' => 'New description']);
//     expect($category->fresh()->slug)->toBe('stable-slug');
// });
