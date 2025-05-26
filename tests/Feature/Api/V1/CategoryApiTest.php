<?php

use App\Models\Category;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper to get API URL
function categoryApiUrl(string $suffix = ''): string
{
    return "/api/v1/categories/{$suffix}";
}

it('can get all categories paginated', function () {
    Category::factory()->create(['name' => 'Category Alpha']);
    Category::factory()->create(['name' => 'Category Beta']);

    getJson(categoryApiUrl())
        ->assertOk()
        ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'slug']], 'links', 'meta'])
        ->assertJsonCount(2, 'data');
});

it('can create a new category', function () {
    $categoryData = [
        'name'        => 'New Category',
        'description' => 'A description for the new category.',
    ];

    postJson(categoryApiUrl(), $categoryData)
        ->assertCreated()
        ->assertJsonPath('data.name', $categoryData['name'])
        ->assertJsonPath('data.slug', 'new-category'); // Slug should be auto-generated

    $this->assertDatabaseHas('categories', ['name' => $categoryData['name']]);
});

it('cannot create a category with a duplicate name', function () {
    Category::factory()->create(['name' => 'Existing Category']);
    $categoryData = ['name' => 'Existing Category'];

    postJson(categoryApiUrl(), $categoryData)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('cannot create a category with a duplicate slug if slug is provided', function () {
    Category::factory()->create(['name' => 'Some Name', 'slug' => 'existing-slug']);
    $categoryData = ['name' => 'Another Name', 'slug' => 'existing-slug'];

    postJson(categoryApiUrl(), $categoryData)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);
});

// it('can get a specific category', function () {
//     $category = Category::factory()->create();

//     getJson(categoryApiUrl($category->slug)) // Using slug for route model binding
//         ->assertOk()
//         ->assertJsonPath('data.id', $category->id)
//         ->assertJsonPath('data.name', $category->name);
// });

// it('returns 404 if category not found by slug', function () {
//     getJson(categoryApiUrl('non-existent-slug'))->assertNotFound();
// });

// it('can update a category', function () {
//     $category   = Category::factory()->create(['name' => 'Old Name']);
//     $updateData = [
//         'name'        => 'Updated Name',
//         'description' => 'Updated description.',
//     ];

//     patchJson(categoryApiUrl($category->slug), $updateData)
//         ->assertOk()
//         ->assertJsonPath('data.name', $updateData['name'])
//         ->assertJsonPath('data.slug', 'updated-name'); // Slug should update if name changes and slug not provided

//     $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $updateData['name']]);
// });

// it('can delete a category and its tasks associations are removed', function () {
//     $category = Category::factory()->create();
//     $task     = Task::factory()->create();
//     $category->tasks()->attach($task->id);

//     $this->assertDatabaseHas('category_task', ['category_id' => $category->id, 'task_id' => $task->id]);

//     deleteJson(categoryApiUrl($category->slug))->assertNoContent();

//     $this->assertDatabaseMissing('categories', ['id' => $category->id]);
//     $this->assertDatabaseMissing('category_task', ['category_id' => $category->id]); // Pivot entries should be gone
//     $this->assertDatabaseHas('tasks', ['id' => $task->id]);                          // Task itself should not be deleted
// });
