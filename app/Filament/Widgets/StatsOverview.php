<?php

namespace App\Filament\Widgets;

use DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        return [
            Card::make('Movies', DB::table('movies')->count()),
            Card::make('Cast', DB::table('cast')->count()),
            Card::make('Crew', DB::table('crew')->count()),
            Card::make('Genres', DB::table('genres')->count()),
            Card::make('Keywords', DB::table('keywords')->count()),
        ];
    }
}
