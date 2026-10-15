<?php

namespace App\Filament\Resources\Parts;

use App\Filament\Resources\Parts\Pages\CreatePart;
use App\Filament\Resources\Parts\Pages\EditPart;
use App\Filament\Resources\Parts\Pages\ListParts;
use App\Models\Official;
use App\Models\Part;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PartResource extends Resource
{
    protected static ?string $model = Part::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Dữ liệu';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'tỉnh thành';

    protected static ?string $pluralModelLabel = 'Tỉnh thành';

    protected static ?string $navigationLabel = 'Tỉnh thành';

    protected static ?string $recordTitleAttribute = 'title';

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('officials');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                    Section::make('Thông tin tỉnh thành')->columnSpan(['lg' => 2])->schema([
                        TextInput::make('title')->label('Tên')->required()->maxLength(255),
                        TextInput::make('alias')->label('Alias (đường dẫn, tự sinh)')
                            ->visibleOn('edit')->disabled()->dehydrated(false),
                        TextInput::make('note')->label('Ghi chú')->maxLength(255),
                        Toggle::make('status')->label('Hiển thị')->default(true),
                    ]),
                    Section::make('Thống kê')->columnSpan(['lg' => 1])->visibleOn('edit')->schema([
                        Text::make(fn (?Part $record) => $record
                            ? number_format($record->officials()->count()).' thẻ' : '')->weight('bold')->size('lg'),
                        Text::make(function (?Part $record) {
                            $last = $record?->officials()->max('created_at');

                            return $last
                                ? 'Thẻ mới nhất: '.Carbon::parse($last)->locale('vi')->diffForHumans()
                                : 'Chưa có thẻ nào';
                        })->color('gray'),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('weight')
            ->reorderable('weight')
            ->columns([
                TextColumn::make('title')->label('Tỉnh thành')->weight('semibold')
                    ->description(fn (Part $p) => $p->alias)
                    ->searchable(['title', 'alias'])->sortable(),
                ViewColumn::make('officials_count')->label('Số thẻ')
                    ->view('filament.tables.count-bar')
                    ->viewData(['max' => (int) Official::query()->selectRaw('count(*) c')->groupBy('part_id')->orderByDesc('c')->value('c')])
                    ->sortable(),
                TextColumn::make('note')->label('Ghi chú')->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('status')->label('Hiển thị'),
            ])
            ->recordUrl(fn (Part $p) => static::getUrl('edit', ['record' => $p]))
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (Part $p) => $p->officials_count > 0)
                    ->tooltip(fn (Part $p) => $p->officials_count > 0 ? 'Còn '.number_format($p->officials_count).' thẻ, không thể xóa' : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->before(function (DeleteBulkAction $action, Collection $records) {
                        $used = $records->filter(fn ($p) => $p->officials_count > 0);
                        if ($used->isNotEmpty()) {
                            Notification::make()->danger()->title('Không thể xóa')
                                ->body('Tỉnh thành còn thẻ: '.$used->pluck('title')->implode(', '))->send();
                            $action->halt();
                        }
                    }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParts::route('/'),
            'create' => CreatePart::route('/create'),
            'edit' => EditPart::route('/{record}/edit'),
        ];
    }
}
