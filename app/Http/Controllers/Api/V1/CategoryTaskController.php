<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskCollection;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryTaskController extends Controller
{
    /**
     * Display a listing of tasks for a specific category.
     */
    public function index(Request $request, Category $category)
    {
        $tasks = $category->tasks() // Accesses the related tasks
            ->with('categories')        // Eager load categories for each task as well
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
}
