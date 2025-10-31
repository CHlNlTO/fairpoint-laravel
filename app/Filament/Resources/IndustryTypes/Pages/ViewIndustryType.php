<?php

namespace App\Filament\Resources\IndustryTypes\Pages;

use App\Filament\Resources\IndustryTypes\IndustryTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIndustryType extends ViewRecord
{
    protected static string $resource = IndustryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
