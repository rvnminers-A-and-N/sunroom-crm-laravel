<?php

namespace Database\Factories;

use App\Models\AiInsight;
use App\Models\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AiInsight> */
class AiInsightFactory extends Factory
{
    public function definition(): array
    {
        return [
            'deal_id' => Deal::factory(),
            'insight' => fake()->paragraph(3),
            'generated_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
