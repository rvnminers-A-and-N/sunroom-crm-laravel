<?php

namespace Database\Factories;

use App\Enums\DealStage;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Deal> */
class DealFactory extends Factory
{
    public function definition(): array
    {
        $stage = fake()->randomElement(DealStage::cases());

        return [
            'user_id' => User::factory(),
            'contact_id' => Contact::factory(),
            'title' => fake()->catchPhrase(),
            'value' => fake()->randomFloat(2, 1000, 500000),
            'stage' => $stage,
            'expected_close_date' => fake()->dateTimeBetween('now', '+90 days'),
            'closed_at' => in_array($stage, [DealStage::Won, DealStage::Lost]) ? fake()->dateTimeBetween('-30 days', 'now') : null,
        ];
    }
}
