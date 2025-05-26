<?php

use App\Http\Controllers\TaskViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/tasks-board');
});

// Task Board Route
Route::get('/tasks-board', [TaskViewController::class, 'board'])
    ->name('tasks.board');

// Route::get('/tasks-list', [TaskViewController::class, 'index'])
//     ->name('tasks-list.index');
