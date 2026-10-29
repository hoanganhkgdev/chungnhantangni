<?php

namespace App\Filament\Resources\Officials\Schemas;

use App\Models\Official;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OfficialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                    Group::make()->columnSpan(['lg' => 2])->schema([
                        Section::make('Thông tin thẻ')->columns(2)->schema([
                            TextInput::make('sothe')->label('Số thẻ')->required()->maxLength(50)
                                ->live(onBlur: true)
                                ->helperText(fn ($state, ?Official $record) => self::dupWarning('sothe', $state, $record, 'số thẻ')),
                            TextInput::make('sochungnhan')->label('Số chứng nhận')->required()->maxLength(50),
                            TextInput::make('phapdanh')->label('Pháp danh')->required()->columnSpanFull(),
                            Select::make('part_id')->label('Tỉnh thành')
                                ->relationship('part', 'title')->searchable()->preload()->required(),
                            TextInput::make('hephai')->label('Hệ phái')
                                ->datalist(['Bắc Tông', 'Nam Tông', 'Khất Sĩ']),
                            TextInput::make('thogioi')->label('Thọ giới'),
                            TextInput::make('gioidan')->label('Tên giới đàn'),
                            Grid::make(3)->columnSpanFull()->schema([
                                TextInput::make('ngaycap_ngay')->label('Ngày cấp')->numeric()->minValue(1)->maxValue(31)->required(),
                                TextInput::make('ngaycap_thang')->label('Tháng')->numeric()->minValue(1)->maxValue(12)->required(),
                                TextInput::make('ngaycap_nam')->label('Năm')->numeric()->minValue(2000)->maxValue(2100)->required(),
                            ]),
                        ]),
                        Section::make('Thông tin cá nhân')->columns(2)->schema([
                            TextInput::make('ho')->label('Họ và đệm')->required(),
                            TextInput::make('ten')->label('Tên')->required(),
                            TextInput::make('ngaysinh')->label('Ngày sinh (dd/mm/yyyy)'),
                            TextInput::make('noisinh')->label('Nơi sinh'),
                            TextInput::make('cccd')->label('Số CCCD')
                                ->live(onBlur: true)
                                ->helperText(fn ($state, ?Official $record) => self::dupWarning('cccd', $state, $record, 'CCCD')),
                            TextInput::make('ngaycapcccd')->label('Ngày cấp CCCD'),
                            TextInput::make('diachi')->label('Nơi tu học')->columnSpanFull(),
                            TextInput::make('thuongtru')->label('Nơi thường trú')->columnSpanFull(),
                        ]),
                    ]),
                    Group::make()->columnSpan(['lg' => 1])->schema([
                        Section::make('Hiển thị')->schema([
                            Toggle::make('status')->label('Cho phép xem khi quét QR')->default(true),
                            TextInput::make('prior')->label('Ưu tiên (cao hiện trước)')->numeric()->default(0),
                        ]),
                        Section::make('Tệp đính kèm')->schema([
                            FileUpload::make('image')->label('Ảnh thẻ')
                                ->disk('uploads')->directory('official')->visibility('public')
                                ->image()->imageEditor()->maxSize(5120)
                                // Lưu tên tệp thuần (không kèm thư mục) như dữ liệu cũ.
                                ->afterStateHydrated(fn ($component, $state) => $component->state($state ? 'official/'.ltrim($state, '/') : null))
                                ->dehydrateStateUsing(fn ($state) => $state ? basename($state) : ''),
                            FileUpload::make('file_pdf')->label('Đơn cấp giấy chứng nhận (PDF)')
                                ->disk('uploads')->directory('official')->visibility('public')
                                ->acceptedFileTypes(['application/pdf'])->maxSize(10240)
                                ->afterStateHydrated(fn ($component, $state) => $component->state($state ? 'official/'.ltrim($state, '/') : null))
                                ->dehydrateStateUsing(fn ($state) => $state ? basename($state) : ''),
                        ]),
                    ]),
                ]),
            ]);
    }

    /** Cảnh báo (không chặn lưu) khi giá trị đã có ở thẻ khác. */
    private static function dupWarning(string $col, ?string $state, ?Official $record, string $label): ?HtmlString
    {
        $state = trim((string) $state);
        if ($state === '') {
            return null;
        }
        $others = Official::where($col, $state)->when($record, fn ($q) => $q->where('id', '!=', $record->id))->limit(5)->get();

        return $others->isEmpty() ? null : new HtmlString(
            '<span style="color:#b45309">⚠ Trùng '.$label.' với thẻ '.$others->map(fn ($o) => '#'.$o->id.' ('.e($o->fullname).')')->implode(', ').'. Vẫn lưu được nếu đúng ý.</span>'
        );
    }
}
