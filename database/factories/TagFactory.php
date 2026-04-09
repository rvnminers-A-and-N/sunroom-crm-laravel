<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tag> */
class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'VIP', 'Hot Lead', 'Decision Maker', 'Referral',
                'Follow Up', 'Cold', 'Partner', 'Enterprise',
            ]),
            'color' => fake()->hexColor(),
        ];
    }
}
