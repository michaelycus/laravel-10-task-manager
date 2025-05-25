<?php
namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskViewController extends Controller
{
    public function board(Request $request)
    {
        // $tasks = Task::with('categories')
        //     ->orderBy('created_at', 'desc')
        //     ->take(50) // Example: take a reasonable number for initial load
        //     ->get();

        return Inertia::render('Tasks/TaskBoard', [
            // 'initialTasks' => TaskResource::collection($tasks)->resolve(), // Resolve to get array
        ]);
    }

    // Example for a traditional list view (if you create one)
    public function index(Request $request)
    {
        // This would be for a paginated list view, not the board
        $tasks = Task::with('categories')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Tasks/Index', [ // Assuming Tasks/Index.vue page
            'tasks' => TaskResource::collection($tasks)->additional([
                'meta' => ['links' => $tasks->links()], // For pagination links
            ]),
        ]);
    }
}
