<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Officials\OfficialResource;
use App\Filament\Widgets\IssuedByYear;
use App\Filament\Widgets\LatestOfficials;
use App\Filament\Widgets\OfficialStats;
use App\Filament\Widgets\TopParts;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Bảng điều khiển';

    public function getHeading(): string|Htmlable|null
    {
        return 'Xin chào, '.(auth()->user()->name ?: 'bạn');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Tổng quan thẻ chứng nhận tăng ni · '.now()->locale('vi')->isoFormat('dddd, DD/MM/YYYY');
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 2];
    }

    public function getWidgets(): array
    {
        return [OfficialStats::class, IssuedByYear::class, TopParts::class, LatestOfficials::class];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('all')->label('Danh sách thẻ')->icon('heroicon-o-identification')->color('gray')
                ->url(OfficialResource::getUrl('index')),
            Action::make('create')->label('Thêm thẻ mới')->icon('heroicon-o-plus')
                ->url(OfficialResource::getUrl('create'))
                ->visible(fn () => auth()->user()->canEdit()),
        ];
    }
}
