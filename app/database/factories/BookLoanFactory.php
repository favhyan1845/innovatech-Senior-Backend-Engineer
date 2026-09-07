<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookLoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $borrowedAt = $this->faker->dateTimeBetween('-40 days', '-1 day');

        return [
            'borrowed_at' => $borrowedAt,
            'due_at' => \Carbon\Carbon::parse($borrowedAt)->addDays(14),
            'returned_at' => null,
        ];
    }

    public function returned()
    {
        return $this->state(function (array $attributes) {
            return [
                'returned_at' => \Carbon\Carbon::parse($attributes['borrowed_at'])->addDays(rand(3, 12)),
            ];
        });
    }
}
