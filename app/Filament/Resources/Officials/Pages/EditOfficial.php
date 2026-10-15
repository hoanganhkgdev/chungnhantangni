<?php

namespace App\Filament\Resources\Officials\Pages;

use App\Filament\Resources\Officials\OfficialResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOfficial extends EditRecord
{
    protected static string $resource = OfficialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')->label('Xem thẻ (QR)')->icon('heroicon-o-qr-code')->color('gray')
                ->url($this->record->public_path)->openUrlInNewTab(),
            Action::make('print')->label('Xuất ảnh thẻ')->icon('heroicon-o-photo')->color('gray')
                ->url(route('official.print', ['ids' => $this->record->id]))->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
