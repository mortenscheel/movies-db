<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class Keyword extends Model
{
    use HasFactory, Searchable;

    protected $guarded = [];

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class);
    }

    #[SearchUsingFullText(['name'])]
    public function toSearchableArray(): array
    {
        return $this->only('name');
    }
}
