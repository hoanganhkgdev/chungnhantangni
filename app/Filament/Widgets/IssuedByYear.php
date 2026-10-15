<?php

namespace App\Filament\Widgets;

use App\Models\Official;
use Filament\Widgets\ChartWidget;

class IssuedByYear extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Thẻ cấp theo năm';

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = Official::where('ngaycap_nam', '>=', 2000)
            ->selectRaw('ngaycap_nam y, count(*) c')->groupBy('y')->orderBy('y')->pluck('c', 'y');

        return [
            'datasets' => [[
                'label' => 'Số thẻ',
                'data' => $rows->values()->all(),
                'backgroundColor' => '#1fa059',
                'borderRadius' => 6,
                'maxBarThickness' => 56,
            ]],
            'labels' => $rows->keys()->map(fn ($y) => (string) $y)->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
