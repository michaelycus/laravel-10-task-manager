<?php

use App\Models\Category;
use App\Models\Task;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

// Helper to get API URL
function taskApiUrl(string $suffix = ''): string
{
    return "/api/v1/tasks/{$suffix}";
}

it('can get all tasks paginated with default sorting and includes categories', function () {
    // Arrange: Create some tasks and categories
    $category1 = Category::factory()->create(['name' => 'Work']);
    $category2 = Category::factory()->create(['name' => 'Personal']);

    $task1 = Task::factory()->create(['name' => 'Task Alpha', 'created_at' => now()->subDay()]);
    $task2 = Task::factory()->create(['name' => 'Task Beta', 'created_at' => now()]);
    $task1->categories()->attach($category1->id);
    $task2->categories()->attach([$category1->id, $category2->id]);

    // Act
    $response = getJson(taskApiUrl());

    // Assert
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'priority', 'is_completed', 'categories' => ['*' => ['id', 'name']]],
            ],
            'links',
            'meta',
        ])
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Task Beta') // Default sort is created_at desc
        ->assertJsonPath('data.0.categories.0.name', $category1->name)
        ->assertJsonPath('data.1.name', 'Task Alpha');
});

it('can create a new task with categories', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $taskData = [
        'name'         => 'New Awesome Task',
        'description'  => 'This is a very important task.',
        'due_date'     => now()->addWeek()->format('Y-m-d'),
        'priority'     => 'high',
        'category_ids' => [$category1->id, $category2->id],
    ];

    $response = postJson(taskApiUrl(), $taskData);

    $response->assertCreated() // HTTP 201
        ->assertJsonPath('data.name', $taskData['name'])
        ->assertJsonPath('data.priority', 'high')
        ->assertJsonCount(2, 'data.categories');

    $this->assertDatabaseHas('tasks', ['name' => $taskData['name']]);
    $this->assertDatabaseCount('category_task', 2); // Check pivot table entries
});

it('cannot create a task with invalid data', function (array $invalidData, array | string $expectedErrors) {
    // Arrange: Ensure at least one category exists for 'category_ids' validation
    Category::factory()->create();

    $response = postJson(taskApiUrl(), $invalidData);

    $response->assertUnprocessable() // HTTP 422
        ->assertJsonValidationErrors($expectedErrors);
})->with([
    'missing name'             => [['priority' => 'low'], 'name'],
    'invalid priority'         => [['name' => 'Test', 'priority' => 'urgent'], 'priority'],
    'invalid due_date format'  => [['name' => 'Test', 'priority' => 'low', 'due_date' => 'tomorrow'], 'due_date'],
    'non-existent category_id' => [['name' => 'Test', 'priority' => 'low', 'category_ids' => [999]], 'category_ids.0'],
]);

it('can get a specific task with its categories', function () {
    $category = Category::factory()->create();
    $task     = Task::factory()->create();
    $task->categories()->attach($category->id);

    $response = getJson(taskApiUrl($task->id));

    $response->assertOk()
        ->assertJsonPath('data.id', $task->id)
        ->assertJsonPath('data.name', $task->name)
        ->assertJsonCount(1, 'data.categories')
        ->assertJsonPath('data.categories.0.id', $category->id);
});

it('returns 404 if task not found for show', function () {
    getJson(taskApiUrl(999))->assertNotFound();
});

it('can update a task including its categories and completion status', function () {
    $task      = Task::factory()->create(['priority' => 'low', 'completed_at' => null]);
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $task->categories()->attach($category1->id); // Initial category

    $updateData = [
        'name'              => 'Updated Task Name',
        'priority'          => 'medium',
        'category_ids'      => [$category2->id], // Change category
        'mark_as_completed' => true,
    ];

    $response = patchJson(taskApiUrl($task->id), $updateData);

    $response->assertOk()
        ->assertJsonPath('data.name', $updateData['name'])
        ->assertJsonPath('data.priority', 'medium')
        ->assertJsonPath('data.is_completed', true)
        ->assertJsonCount(1, 'data.categories') // Should now have only category2
        ->assertJsonPath('data.categories.0.id', $category2->id);

    $this->assertDatabaseHas('tasks', [
        'id'       => $task->id,
        'name'     => $updateData['name'],
        'priority' => 'medium',
    ]);
    expect($task->fresh()->completed_at)->not->toBeNull();
    $this->assertDatabaseCount('category_task', 1);
    $this->assertDatabaseHas('category_task', ['task_id' => $task->id, 'category_id' => $category2->id]);
    $this->assertDatabaseMissing('category_task', ['task_id' => $task->id, 'category_id' => $category1->id]);
});

it('can mark a task as pending if it was completed', function () {
    $task = Task::factory()->create(['completed_at' => now()]);

    $updateData = [
        'mark_as_completed' => false,
    ];

    $response = patchJson(taskApiUrl($task->id), $updateData);

    $response->assertOk()
        ->assertJsonPath('data.is_completed', false);

    expect($task->fresh()->completed_at)->toBeNull();
});

it('cannot update a task with invalid data', function () {
    $task       = Task::factory()->create();
    $updateData = ['priority' => 'invalid_priority'];

    patchJson(taskApiUrl($task->id), $updateData)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['priority']);
});

it('can delete a task', function () {
    $task     = Task::factory()->create();
    $category = Category::factory()->create();
    $task->categories()->attach($category->id);

    deleteJson(taskApiUrl($task->id))->assertNoContent(); // HTTP 204

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    $this->assertDatabaseMissing('category_task', ['task_id' => $task->id]); // Pivot entries should be gone
});

it('can filter tasks by name', function () {
    Task::factory()->create(['name' => 'Specific Task To Find']);
    Task::factory()->create(['name' => 'Another Task']);

    getJson(taskApiUrl('?name=Specific'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Specific Task To Find');
});

it('can filter tasks by priority', function () {
    Task::factory()->create(['priority' => 'high']);
    Task::factory()->create(['priority' => 'low']);

    getJson(taskApiUrl('?priority=high'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.priority', 'high');
});

it('can filter tasks by status completed', function () {
    Task::factory()->create(['name' => 'Completed Task', 'completed_at' => now()]);
    Task::factory()->create(['name' => 'Pending Task', 'completed_at' => null]);

    getJson(taskApiUrl('?status=completed'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Completed Task');
});

it('can filter tasks by status pending', function () {
    Task::factory()->create(['name' => 'Completed Task', 'completed_at' => now()]);
    Task::factory()->create(['name' => 'Pending Task', 'completed_at' => null]);

    getJson(taskApiUrl('?status=pending'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Pending Task');
});

it('can sort tasks by name ascending', function () {
    Task::factory()->create(['name' => 'Zulu Task']);
    Task::factory()->create(['name' => 'Alpha Task']);

    getJson(taskApiUrl('?sort_by=name&sort_direction=asc'))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Alpha Task')
        ->assertJsonPath('data.1.name', 'Zulu Task');
});

it('can sort tasks by due_date descending', function () {
    Task::factory()->create(['name' => 'Due Later', 'due_date' => Carbon::tomorrow()]);
    Task::factory()->create(['name' => 'Due Sooner', 'due_date' => Carbon::today()]);

    getJson(taskApiUrl('?sort_by=due_date&sort_direction=desc'))
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Due Later')
        ->assertJsonPath('data.1.name', 'Due Sooner');
});
