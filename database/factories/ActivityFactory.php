<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id'  => Company::factory(),
            'guide_id'    => User::factory()->guide(),
            'name'        => fake()->name(),
            'description' => fake()->text(),
            'start_time'  => fake()->dateTimeBetween('+1 day', '+1 year'),
            'price'       => fake()->randomNumber(5, strict: true),
        ];
    }
}
