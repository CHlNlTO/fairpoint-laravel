<?php

namespace App\Filament\Resources\IndustryTypes\Pages;

use App\Filament\Resources\IndustryTypes\IndustryTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIndustryTypes extends ListRecords
{
    protected static string $resource = IndustryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
