<?php

use App\Models\Movie;
use App\Models\User;
use Database\Seeders\MoviesSeeder;
use function Pest\Laravel\seed;

it('requires sanctum token', function () {
    apiRequest()->get('/api/movies')->assertStatus(401);
});

it('can list movies', function () {
    seed(MoviesSeeder::class);
    apiRequest(User::factory())
        ->get('api/movies')
        ->assertOk()
        ->assertJsonCount(10, 'data');
});

it('can show movie', function () {
    seed(MoviesSeeder::class);
    $movie = Movie::firstOrFail();
    $response = apiRequest(User::factory())
        ->get("/api/movies/$movie->id")
        ->assertOk();
    $this->assertEquals($movie->title, $response->json('title'));
});

it('allows updates from creator', function () {
    seed(MoviesSeeder::class);
    $movie = Movie::firstOrFail();
    apiRequest(User::factory(['id' => 1]))
        ->put("/api/movies/$movie->id", ['title' => 'foo'])
        ->assertOk()
        ->assertJsonFragment(['title' => 'foo']);
    $this->assertDatabaseHas('movies', ['id' => $movie->id, 'title' => 'foo']);
});

it('denies updates from non-creator', function () {
    seed(MoviesSeeder::class);
    $movie = Movie::firstOrFail();
    apiRequest(User::factory(['id' => 2]))
        ->put("/api/movies/$movie->id", ['title' => 'foo'])
        ->assertForbidden();
});

it('allows deletion by creator', function () {
    seed(MoviesSeeder::class);
    $movie = Movie::firstOrFail();
    apiRequest(User::factory(['id' => 1]))
        ->delete("/api/movies/$movie->id")
        ->assertOk();
    $this->assertModelMissing($movie);
});

it('denies deletion by non-creator', function () {
    seed(MoviesSeeder::class);
    $movie = Movie::firstOrFail();
    apiRequest(User::factory(['id' => 2]))
        ->delete("/api/movies/$movie->id")
        ->assertForbidden();
    $this->assertModelExists($movie);
});

it('can create new movies', function () {
    $user = User::factory(['id' => 2])->create();
    apiRequest($user)
        ->post('api/movies', [
            'title' => 'foo',
            'tagline' => 'bar',
            'description' => 'The foo bar',
            'poster' => '',
            'budget' => 100,
            'revenue' => 200,
            'runtime' => 120,
            'popularity' => 10,
            'vote_average' => 10,
            'vote_count' => 1,
            'imdb_id' => 'tt1234567',
            'homepage' => null,
            'release_date' => now()->toDateString(),
        ])
        ->assertOk();
    $this->assertDatabaseHas('movies', ['user_id' => $user->id, 'title' => 'foo']);
});
