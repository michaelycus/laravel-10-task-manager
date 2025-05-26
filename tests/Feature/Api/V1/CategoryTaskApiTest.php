<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// it('can get tasks for a specific category', function () {
//     $category1 = Category::factory()->create(['name' => 'Work']);
//     $category2 = Category::factory()->create(['name' => 'Personal']);

//     $task1 = Task::factory()->create(['name' => 'Work Task 1']);
//     $task2 = Task::factory()->create(['name' => 'Personal Task']);
//     $task3 = Task::factory()->create(['name' => 'Work Task 2']);

//     $category1->tasks()->attach([$task1->id, $task3->id]);
//     $category2->tasks()->attach($task2->id);

//     // Act: Get tasks for Category 1
//     $response = getJson("/api/v1/categories/{$category1->slug}/tasks");

//     // Assert
//     $response->assertOk()
//         ->assertJsonCount(2, 'data')
//         ->assertJsonPath('data.0.name', $task3->name) // Assuming default sort by created_at desc
//         ->assertJsonPath('data.1.name', $task1->name)
//         ->assertJsonMissing(['name' => 'Personal Task']); // Ensure tasks from other categories are not present

//     // Check filtering within nested resource
//     $responseFiltered = getJson("/api/v1/categories/{$category1->slug}/tasks?name=Work Task 1");
//     $responseFiltered->assertOk()
//         ->assertJsonCount(1, 'data')
//         ->assertJsonPath('data.0.name', 'Work Task 1');
// });

// it('returns an empty list if category has no tasks', function () {
//     $category = Category::factory()->create();

//     getJson("/api/v1/categories/{$category->slug}/tasks")
//         ->assertOk()
//         ->assertJsonCount(0, 'data');
// });

// it('returns 404 if category for tasks list does not exist', function () {
//     getJson("/api/v1/categories/non-existent-slug/tasks")->assertNotFound();
// });
