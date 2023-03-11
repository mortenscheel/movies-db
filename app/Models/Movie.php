<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'adult' => 'bool',
        'released_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class);
    }

    public function cast(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'cast')->withPivot([
            'character',
            'order',
        ]);
    }

    public function crew(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'crew')->withPivot(['job']);
    }
}
