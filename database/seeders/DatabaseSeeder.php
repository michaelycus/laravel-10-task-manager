<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a default user if not using Jetstream's default
        if (User::count() == 0) {
            User::factory()->create([
                'name'  => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Create Categories
        $categories = Category::factory(5)->create();

        // Create Tasks and associate them with categories
        Task::factory(20)->create()->each(function ($task) use ($categories) {
            // Attach 1 to 3 random categories to each task
            $task->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
