<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexMovieRequest;
use App\Http\Requests\ShowMovieRequest;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Http\Resources\MovieApiResource;
use App\Models\Movie;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[Group('Movies')]
class MovieController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Movie::class);
    }

    #[Endpoint('List movies')]
    #[ResponseFromApiResource(
        MovieApiResource::class,
        Movie::class, collection: true,
        with: ['genres', 'keywords', 'companies', 'cast', 'crew'],
        paginate: 15)
    ]
    public function index(IndexMovieRequest $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Movie::class)
            ->allowedFields(self::getAllowedFields())
            ->allowedIncludes(self::getAllowedIncludes())
            ->allowedFilters(self::getAllowedFilters())
            ->allowedSorts(self::getAllowedSorts())
            ->defaultSort('title')
            ->paginate(min(100, $request->get('per_page')));

        return MovieApiResource::collection($query);
    }

    #[Endpoint('Store movie')]
    #[ResponseFromApiResource(
        MovieApiResource::class,
        Movie::class,
    )]
    public function store(StoreMovieRequest $request): MovieApiResource
    {
        return new MovieApiResource(Movie::create([
            'user_id' => Auth::id(),
            ...$request->validated(),
        ])->fresh());
    }

    /** @noinspection PhpUnusedParameterInspection */
    #[Endpoint('Show movie')]
    #[ResponseFromApiResource(
        MovieApiResource::class,
        Movie::class,
        with: ['genres', 'cast'],
    )]
    public function show(Movie $movie, ShowMovieRequest $request): MovieApiResource
    {
        return new MovieApiResource(
            QueryBuilder::for(Movie::class)
                ->where('id', $movie->id)
                ->allowedFields(self::getAllowedFields())
                ->allowedIncludes(self::getAllowedIncludes())
                ->firstOrFail()
        );
    }

    #[Endpoint('Update movie')]
    #[ResponseFromApiResource(
        MovieApiResource::class,
        Movie::class,
    )]
    public function update(UpdateMovieRequest $request, Movie $movie): MovieApiResource
    {
        $movie->update($request->validated());

        return new MovieApiResource($movie);
    }

    #[Endpoint('Delete movie')]
    #[Response(['deleted' => true])]
    public function destroy(Movie $movie): JsonResponse
    {
        $movie->delete();

        return \response()->json(['deleted' => true]);
    }

    public static function getAllowedFields(): array
    {
        return [
            'id',
            'title',
            'tagline',
            'description',
            'poster',
            'budget',
            'revenue',
            'runtime',
            'popularity',
            'vote_average',
            'vote_count',
            'imdb_id',
            'homepage',
            'release_date',
        ];
    }

    public static function getAllowedIncludes(): array
    {
        return [
            'genres',
            'companies',
            'keywords',
            'cast',
            'crew',
            'user',
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            'id',
            'title',
            'runtime',
            'popularity',
            'release_date',
        ];
    }

    public static function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            'title',
        ];
    }
}
