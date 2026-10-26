<?php

namespace App\Filament\Resources\Officials\Pages;

use App\Filament\Resources\Officials\OfficialResource;
use App\Models\Official;
use App\Models\Part;
use App\Support\OfficialImages;
use App\Support\OfficialImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ListOfficials extends ListRecords
{
    protected static string $resource = OfficialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('template')->label('Tải file mẫu')->visible(fn () => auth()->user()->canEdit())->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->action(function () {
                    $path = tempnam(sys_get_temp_dir(), 'tpl');
                    OfficialImporter::writeTemplate($path);

                    return response()->download($path, 'mau-import-the-tang-ni.xlsx')->deleteFileAfterSend();
                }),
            Action::make('import')->label('Import Excel')->visible(fn () => auth()->user()->canEdit())->icon('heroicon-o-arrow-up-tray')->color('success')
                ->modalHeading('Import thẻ tăng ni từ Excel')
                ->modalDescription('Cột được nhận theo tên tiêu đề, không phụ thuộc thứ tự. Bắt buộc có "Họ và tên", "Ngày cấp" (dd/mm/yyyy) và "Tỉnh thành". Dùng "Tải file mẫu" để lấy đúng tiêu đề.')
                ->modalSubmitActionLabel('Import')
                ->schema([
                    FileUpload::make('file')->label('File .xlsx hoặc .csv')->required()
                        ->disk('local')->directory('imports')->visibility('private')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/csv', 'text/plain', 'application/csv',
                        ]),
                    Select::make('part_id')->label('Tỉnh thành mặc định')
                        ->helperText('Dùng cho dòng có cột "Tỉnh thành" để trống hoặc không khớp tên.')
                        ->options(fn () => Part::orderBy('weight')->pluck('title', 'id'))->searchable(),
                ])
                ->action(function (array $data) {
                    $path = Storage::disk('local')->path($data['file']);
                    try {
                        $r = (new OfficialImporter($data['part_id'] ?? null))
                            ->import($path, pathinfo($path, PATHINFO_EXTENSION));
                    } finally {
                        Storage::disk('local')->delete($data['file']);
                    }

                    $body = "Thêm mới: {$r['created']} · Cập nhật: {$r['updated']} · Bỏ qua: {$r['skipped']}";
                    if ($r['errors']) {
                        $body .= '<br>'.e(implode(' | ', array_slice($r['errors'], 0, 15)))
                            .(count($r['errors']) > 15 ? ' …' : '');
                    }

                    Notification::make()->title('Import xong')->body(new HtmlString($body))
                        ->status($r['skipped'] || $r['errors'] ? 'warning' : 'success')->persistent()->send();
                }),
            Action::make('uploadImages')->label('Upload ảnh hàng loạt')->visible(fn () => auth()->user()->canEdit())->icon('heroicon-o-photo')->color('info')
                ->modalHeading('Upload ảnh thẻ hàng loạt')
                ->modalDescription('Chọn nhiều ảnh cùng lúc. Tên file phải trùng cột "Tên hình" đã điền khi import (không cần đúng đuôi, không phân biệt hoa thường). Ảnh trùng tên sẽ được ghi đè.')
                ->modalSubmitActionLabel('Upload')
                ->schema([
                    FileUpload::make('files')->label('Ảnh')->required()->multiple()->maxFiles(300)
                        ->disk('uploads')->directory('official')->visibility('public')
                        ->image()->maxSize(5120)->preserveFilenames()->panelLayout('grid'),
                ])
                ->action(function (array $data) {
                    $r = OfficialImages::attach(array_values((array) $data['files']));

                    $body = "Đã gán ảnh cho {$r['matched']} thẻ.";
                    if ($r['unmatched']) {
                        $body .= '<br>Không khớp thẻ nào ('.count($r['unmatched']).'): '
                            .e(implode(', ', array_slice($r['unmatched'], 0, 20))).(count($r['unmatched']) > 20 ? ' …' : '');
                    }
                    Notification::make()->title('Upload xong')->body(new HtmlString($body))
                        ->status($r['unmatched'] ? 'warning' : 'success')->persistent()->send();
                }),
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả')->badge(Official::count()),
            'active' => Tab::make('Đang hiển thị')->modifyQueryUsing(fn (Builder $query) => $query->where('status', true)),
            'hidden' => Tab::make('Đã ẩn')->badge(Official::where('status', false)->count())->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', false)),
            'noimage' => Tab::make('Thiếu ảnh')->badge(Official::where('image', '')->count())->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('image', '')),
            'dup' => Tab::make('Thẻ trùng')->badge(Official::duplicated()->count())->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->duplicated()),
        ];
    }
}
