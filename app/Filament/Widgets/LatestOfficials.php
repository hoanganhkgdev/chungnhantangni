<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Officials\OfficialResource;
use App\Models\Official;
use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOfficials extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Thẻ mới nhập';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Official::query()->withDup()->with('part')->orderByDesc('officials.id'))
            ->paginated([8, 15, 30])
            ->defaultPaginationPageOption(8)
            ->columns([
                ImageColumn::make('image')->label('')
                    ->disk('uploads')
                    ->getStateUsing(fn (Official $o) => $o->image ? 'official/'.$o->image : null)
                    ->defaultImageUrl(url('/images/no_avatar.png'))
                    ->circular()->size(44),
                TextColumn::make('phapdanh')->label('Pháp danh')->weight('semibold')
                    ->description(fn (Official $o) => $o->fullname),
                TextColumn::make('sothe')->label('Số thẻ')->fontFamily('mono')
                    ->description(fn (Official $o) => 'CN: '.$o->sochungnhan),
                TextColumn::make('part.title')->label('Tỉnh thành')->badge()->color('gray'),
                TextColumn::make('hephai')->label('Hệ phái')->badge()->color('primary'),
                TextColumn::make('notes')->label('Cần chú ý')->badge()->color('warning')
                    ->state(fn (Official $o) => array_values(array_filter([
                        $o->status ? null : 'Đang ẩn',
                        $o->image ? null : 'Thiếu ảnh',
                        $o->dup_sothe_ids ? 'Trùng số thẻ' : null,
                        $o->dup_cccd_ids ? 'Trùng CCCD' : null,
                    ]))),
                TextColumn::make('created_at')->label('Nhập lúc')->color('gray')
                    ->state(fn (Official $o) => $o->created_at?->locale('vi')->diffForHumans() ?? '—'),
            ])
            ->recordUrl(fn (Official $o) => auth()->user()->canEdit() ? OfficialResource::getUrl('edit', ['record' => $o]) : null)
            ->recordActions([
                Action::make('view')->label('Xem thẻ')->icon('heroicon-o-qr-code')->color('gray')
                    ->url(fn (Official $o) => $o->public_path)->openUrlInNewTab(),
                Action::make('edit')->label('Sửa')->icon('heroicon-o-pencil-square')
                    ->url(fn (Official $o) => OfficialResource::getUrl('edit', ['record' => $o]))
                    ->visible(fn () => auth()->user()->canEdit()),
            ])
            ->headerActions([
                Action::make('all')->label('Xem tất cả')->url(OfficialResource::getUrl('index'))->link(),
            ]);
    }
}
