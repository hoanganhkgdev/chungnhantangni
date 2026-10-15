<?php

namespace App\Filament\Resources\Officials\Tables;

use App\Filament\Resources\Officials\OfficialResource;
use App\Models\Official;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OfficialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('')
                    ->disk('uploads')
                    ->getStateUsing(fn (Official $o) => $o->image ? 'official/'.$o->image : null)
                    ->defaultImageUrl(url('/images/no_avatar.png'))
                    ->circular()->size(44),
                TextColumn::make('phapdanh')->label('Pháp danh')->weight('semibold')
                    ->description(fn (Official $o) => $o->fullname)
                    ->searchable(['phapdanh', 'ho', 'ten'])
                    ->sortable(),
                TextColumn::make('sothe')->label('Số thẻ')
                    ->description(fn (Official $o) => 'CN: '.$o->sochungnhan)
                    ->searchable(['sothe', 'sochungnhan'])->copyable()->fontFamily('mono'),
                TextColumn::make('part.title')->label('Tỉnh thành')->badge()->color('gray')->sortable(),
                TextColumn::make('hephai')->label('Hệ phái')->badge()->color('primary')->toggleable(),
                TextColumn::make('ngaycap_text')->label('Ngày cấp')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('ngaycap_nam', $direction)
                        ->orderBy('ngaycap_thang', $direction)->orderBy('ngaycap_ngay', $direction)),
                TextColumn::make('notes')->label('Cần chú ý')->badge()->color('warning')
                    ->state(fn (Official $o) => array_values(array_filter([
                        $o->status ? null : 'Đang ẩn',
                        $o->image ? null : 'Thiếu ảnh',
                        $o->dup_sothe_ids ? 'Trùng số thẻ với #'.str_replace(',', ', #', $o->dup_sothe_ids) : null,
                        $o->dup_cccd_ids ? 'Trùng CCCD với #'.str_replace(',', ', #', $o->dup_cccd_ids) : null,
                    ]))),
                ToggleColumn::make('status')->label('Hiển thị')->disabled(fn () => ! auth()->user()->canEdit()),
            ])
            ->defaultSort('id', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('part_id')->label('Tỉnh thành')->relationship('part', 'title')->searchable()->preload(),
                SelectFilter::make('hephai')->label('Hệ phái')->options(fn () => Official::query()
                    ->where('hephai', '!=', '')->distinct()->pluck('hephai', 'hephai')->all()),
                SelectFilter::make('ngaycap_nam')->label('Năm cấp')->options(fn () => Official::query()
                    ->where('ngaycap_nam', '>', 0)->distinct()->orderByDesc('ngaycap_nam')->pluck('ngaycap_nam', 'ngaycap_nam')->all()),
                TernaryFilter::make('status')->label('Hiển thị'),
            ])
            ->recordUrl(fn (Official $o) => OfficialResource::getUrl('edit', ['record' => $o]))
            ->recordActions([
                Action::make('view')->label('Xem thẻ')->icon('heroicon-o-qr-code')
                    ->url(fn (Official $o) => $o->public_path)->openUrlInNewTab(),
                Action::make('print')->label('Xuất ảnh')->icon('heroicon-o-photo')->color('gray')
                    ->url(fn (Official $o) => route('official.print', ['ids' => $o->id]))->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('show')->visible(fn () => auth()->user()->canEdit())->label('Hiển thị')->icon('heroicon-o-eye')
                        ->action(fn (Collection $r) => $r->each->update(['status' => true]))->deselectRecordsAfterCompletion(),
                    BulkAction::make('hide')->visible(fn () => auth()->user()->canEdit())->label('Ẩn')->icon('heroicon-o-eye-slash')
                        ->action(fn (Collection $r) => $r->each->update(['status' => false]))->deselectRecordsAfterCompletion(),
                    BulkAction::make('print')->label('Xuất ảnh thẻ đã chọn')->icon('heroicon-o-photo')
                        ->action(fn (Collection $r, $livewire) => $livewire->js(
                            'window.open('.json_encode(route('official.print', ['ids' => $r->pluck('id')->implode(',')])).', "_blank")'
                        ))->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
