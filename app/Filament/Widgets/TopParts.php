<?php

namespace App\Filament\Widgets;

use App\Models\Part;
use Filament\Widgets\ChartWidget;

class TopParts extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Tỉnh thành nhiều thẻ nhất';

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $parts = Part::withCount('officials')->orderByDesc('officials_count')->limit(8)->get();

        return [
            'datasets' => [[
                'label' => 'Số thẻ',
                'data' => $parts->pluck('officials_count')->all(),
                'backgroundColor' => '#009444',
                'borderRadius' => 6,
                'maxBarThickness' => 22,
            ]],
            'labels' => $parts->pluck('title')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
