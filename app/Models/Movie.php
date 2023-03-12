<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class Movie extends Model
{
    use HasFactory, Searchable;

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

    public function runtime(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $mins = $value % 60;
                $hrs = floor($value / 60);

                return sprintf('%dh%dm', $hrs, $mins);
            },
            set: function ($value) {
                if (preg_match('/(\d+)h(\d+)m/', $value, $match)) {
                    $hrs = (int) $match[1][0];
                    $mins = (int) $match[1][0];
                } elseif (preg_match('/(\d+):(\d+)/', $value, $match)) {
                    $hrs = (int) $match[1][0];
                    $mins = (int) $match[1][0];
                } else {
                    return $value;
                }

                return $hrs * 60 + $mins;
            }
        );
    }

    #[SearchUsingFullText(['title', 'tagline', 'description'])]
    public function toSearchableArray(): array
    {
        return $this->only([
            'title',
            'tagline',
            'description',
        ]);
    }
}
