<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => rtrim($this->faker->sentence(4), '.'),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->unique()->isbn13(),
            'publisher' => $this->faker->company(),
            'category' => $this->faker->randomElement(['Fiction', 'Science', 'Technology', 'History', 'Biography', 'Fantasy', 'Self-help']),
            'language' => 'en',
            'published_year' => $this->faker->numberBetween(1950, (int) date('Y')),
            'total_copies' => $this->faker->numberBetween(1, 5),
            'description' => $this->faker->paragraph(3),
            'cover_url' => null,
        ];
    }
}
