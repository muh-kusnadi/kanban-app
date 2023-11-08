<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create();

        $date = $faker->dateTimeBetween('today', '+30 days');

        $statusOptions = ['not_started', 'in_progress', 'in_review', 'completed'];

        return [
            'name'     => $faker->name,
            'detail'   => $faker->paragraph,
            'due_date' => $date,
            'status'   => $faker->randomElement($statusOptions),
        ];
    }
}
