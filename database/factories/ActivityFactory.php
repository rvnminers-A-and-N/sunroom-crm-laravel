<?php

namespace Database\Factories;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Activity> */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(ActivityType::cases()),
            'subject' => fake()->sentence(4),
            'body' => fake()->optional(0.7)->paragraph(),
            'occurred_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
