<?php
namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'         => $this->faker->sentence(3),
            'description'  => $this->faker->paragraph(2),
            'due_date'     => $this->faker->optional()->dateTimeBetween('+1 day', '+1 month')?->format('Y-m-d'),
            'priority'     => $this->faker->randomElement(['low', 'medium', 'high']),
            'completed_at' => $this->faker->optional(0.3)->dateTimeThisMonth(),
        ];
    }
}
