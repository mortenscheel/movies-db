<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Keyword;
use App\Models\Movie;
use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class MoviesSeeder extends Seeder
{
    use WithFaker;

    public function run(): void
    {
        $this->setUpFaker();
        $genres = Genre::factory(10)->create();
        $keywords = Keyword::factory(30)->create();
        $people = Person::factory(50)->create();
        foreach (Movie::factory(10)->create() as $movie) {
            $movie->genres()->sync($genres->shuffle()->take(2));
            $movie->keywords()->sync($keywords->shuffle()->take(3));
            $cast = $people->shuffle()->take(10)
                ->mapWithKeys(fn (Person $person) => [
                    $person->id => [
                        'character' => $this->faker->name(),
                        'cast_id' => $this->faker->numberBetween(1, 1000),
                        'order' => $this->faker->numberBetween(1, 100),
                    ],
                ]);
            $movie->cast()->sync($cast);
        }
    }
}
