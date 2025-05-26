<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CategoryTaskController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Versioning the API
Route::prefix('v1')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('categories', CategoryController::class);

    // Nested resource: Tasks for a specific category
    // GET /api/v1/categories/{category}/tasks
    Route::get('categories/{category}/tasks', [CategoryTaskController::class, 'index'])->name('categories.tasks.index');
});
