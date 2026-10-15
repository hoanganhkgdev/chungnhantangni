<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Officials\OfficialResource;
use App\Models\Official;
use App\Models\Part;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class OfficialStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $total = Official::count();
        $shown = Official::where('status', true)->count();
        $recent = Official::where('created_at', '>=', now()->subDays(30))->count();

        // Đếm thẻ trùng khá nặng nên nhớ kết quả vài phút.
        $attention = Cache::remember('dashboard.attention', 300, fn () => [
            'dup' => Official::duplicated()->count(),
            'noimage' => Official::where('image', '')->count(),
        ]);
        $needs = $attention['dup'] + $attention['noimage'];

        // Số thẻ thêm mỗi ngày trong 14 ngày gần nhất, vẽ đường nhỏ dưới ô thống kê.
        $byDay = Official::where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('date(created_at) d, count(*) c')->groupBy('d')->pluck('c', 'd');
        $spark = collect(range(13, 0))->map(fn ($i) => (int) ($byDay[now()->subDays($i)->toDateString()] ?? 0))->all();

        return [
            Stat::make('Tổng số thẻ', number_format($total))
                ->description(number_format(Part::where('status', true)->count()).' tỉnh thành đang hoạt động')
                ->descriptionIcon(Heroicon::OutlinedMapPin)
                ->url(OfficialResource::getUrl('index')),
            Stat::make('Đang hiển thị', number_format($shown))
                ->description($total ? round($shown / $total * 100).'% tổng số · '.number_format($total - $shown).' đang ẩn' : '—')
                ->descriptionIcon(Heroicon::OutlinedEye)
                ->color('success')
                ->url(OfficialResource::getUrl('index').'?activeTab=hidden'),
            Stat::make('Thêm trong 30 ngày', number_format($recent))
                ->description('14 ngày gần nhất')
                ->descriptionIcon(Heroicon::OutlinedPlusCircle)
                ->chart($spark)->color('primary'),
            Stat::make('Cần xử lý', number_format($needs))
                ->description($needs
                    ? number_format($attention['dup']).' thẻ trùng · '.number_format($attention['noimage']).' thiếu ảnh'
                    : 'Mọi thẻ đều ổn')
                ->descriptionIcon($needs ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->color($needs ? 'warning' : 'success')
                ->url(OfficialResource::getUrl('index').'?activeTab=dup'),
        ];
    }
}
