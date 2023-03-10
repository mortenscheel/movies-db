<?php

namespace App\Filament\Widgets;

use App\Models\Movie;
use App\Models\Person;
use DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Movies', Movie::count()),
            Card::make('People', Person::count()),
            Card::make('Credits', DB::table('movie_person')->count()),
        ];
    }
}
