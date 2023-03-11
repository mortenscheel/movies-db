<?php

namespace App\Filament\Widgets;

use App\Models\Movie;
use Filament\Widgets\BarChartWidget;

class RevenueChart extends BarChartWidget
{
    protected static ?string $heading = 'Top 10 revenue';

    protected int|string|array $columnSpan = 2;

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Movie::orderBy('revenue', 'desc')->limit(10)->get();
        $datasets = [
            [
                'label' => 'Budget',
                'backgroundColor' => '#fb7185',
                'data' => $data->pluck('budget')->toArray(),
            ],
            [
                'label' => 'Revenue',
                'backgroundColor' => '#a3e635',
                'data' => $data->pluck('revenue')->toArray(),
            ],
        ];
        $labels = $data->pluck('title')->toArray();

        return compact('datasets', 'labels');
    }

    protected function getOptions(): ?array
    {
        return [];
    }
}
