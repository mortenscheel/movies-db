<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Person extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function moviesAsCast(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'cast')->withPivot([
            'character',
            'order',
        ]);
    }

    public function moviesAsCrew(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'crew')->withPivot(['job']);
    }
}
