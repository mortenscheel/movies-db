<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Movie */
class MovieApiResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge($this->only([
            'id',
            'title',
            'tagline',
            'homepage',
            'budget',
            'revenue',
            'popularity',
            'vote_average',
            'vote_count',
            'runtime',
            'release_date',
        ]), [
            'genres' => GenreApiResource::collection($this->whenLoaded('genres')),
            'keywords' => KeywordApiResource::collection($this->whenLoaded('keywords')),
            'companies' => CompanyApiResource::collection($this->whenLoaded('companies')),
            'cast' => CastApiResource::collection($this->whenLoaded('cast')),
            'crew' => CrewApiResource::collection($this->whenLoaded('cast')),
        ]);
    }
}
