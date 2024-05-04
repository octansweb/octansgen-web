<?php

namespace App\Filament\Resources\FormatFieldResource\Pages;

use App\Filament\Resources\FormatFieldResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormatFields extends ListRecords
{
    protected static string $resource = FormatFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
