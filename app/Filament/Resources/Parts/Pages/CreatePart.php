<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Filament\Resources\Parts\PartResource;
use App\Models\Part;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePart extends CreateRecord
{
    protected static string $resource = PartResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Alias dạng "Tinh-Dong-Nai" như dữ liệu cũ.
        $data['alias'] = collect(explode('-', Str::slug($data['title'])))->map(fn ($w) => ucfirst($w))->implode('-');
        $data['weight'] = ((int) Part::max('weight')) + 1;

        return $data;
    }
}
