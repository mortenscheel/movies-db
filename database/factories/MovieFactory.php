<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->catchPhrase(),
            'user_id' => 1,
            'tagline' => $this->faker->catchPhrase(),
            'description' => $this->faker->sentences(2, asText: true),
            'poster' => '',
            'budget' => $this->faker->numberBetween(100000, 500000000),
            'revenue' => $this->faker->numberBetween(100000, 500000000),
            'runtime' => $this->faker->randomFloat(2, 20, 1200),
            'popularity' => $this->faker->numberBetween(0, 550),
            'vote_average' => $this->faker->numberBetween(0, 10),
            'vote_count' => $this->faker->numberBetween(0, 14000),
            'imdb_id' => 'tt'.$this->faker->randomNumber(7, strict: true),
            'homepage' => $this->faker->url(),
            'release_date' => $this->faker->dateTime()->format('Y-m-d'),
        ];
    }

    public function pivotCast(): array
    {
        return [
            'character' => $this->faker->name(),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function pivotCrew(): array
    {
        return [
            'job' => $this->faker->randomElement(['Director', 'Screenwriter']),
        ];
    }
}
