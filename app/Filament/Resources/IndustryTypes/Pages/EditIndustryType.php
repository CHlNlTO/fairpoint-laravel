<?php

namespace App\Filament\Resources\IndustryTypes\Pages;

use App\Filament\Resources\IndustryTypes\IndustryTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIndustryType extends EditRecord
{
    protected static string $resource = IndustryTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
