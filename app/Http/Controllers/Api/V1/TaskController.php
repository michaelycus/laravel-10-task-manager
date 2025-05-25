<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTaskRequest;
use App\Http\Requests\Api\V1\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Optional;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Eager load categories to prevent N+1
        $tasks = Task::with('categories')
            ->filterByName($request->query('name'))
            ->filterByPriority($request->query('priority'))
            ->filterByStatus($request->query('status'))
            ->sortBy(
                $request->query('sort_by', 'created_at'),
                $request->query('sort_direction', 'desc')
            )
            ->paginate($request->query('per_page', 15));

        return new TaskCollection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();
        $task      = null;

        DB::transaction(function () use ($validated, &$task) {
            $task = Task::create($validated);
            if (! empty($validated['category_ids'])) {
                $task->categories()->sync($validated['category_ids']);
            }
        });

        // Eager load categories for the response
        return new TaskResource(Optional::wrap($task)->load('categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // Eager load categories
        return new TaskResource($task->load('categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $task, $request) {
            $task->update($validated);

            if ($request->has('category_ids')) {
                // Check if category_ids was actually sent
                $task->categories()->sync($validated['category_ids'] ?? []);
            }

            if ($request->has('mark_as_completed')) {
                $task->completed_at = $validated['mark_as_completed'] ? now() : null;
                $task->save();
            }
        });

        return new TaskResource($task->load('categories'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->noContent(); // HTTP 204
    }
}
