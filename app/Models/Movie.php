<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'adult' => 'bool',
        'released_at' => 'date',
    ];

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
        return $this->belongsToMany(Person::class)->withPivot([
            'job',
            'character',
            'order',
        ])->using(Credit::class)->where('job', 'Actor');
    }

    public function crew(): BelongsToMany
    {
        return $this->belongsToMany(Person::class)->withPivot(['job'])->using(Credit::class)->where('job', '!=', 'Actor');
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class)->withPivot([
            'job',
            'character',
            'order',
        ])->using(Credit::class);
    }
}
