<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Credit extends Pivot
{
    protected $table = 'movie_person';

    protected $guarded = [];
}
