<?php

namespace App\Filament\Resources\BusinessTypes\Pages;

use App\Filament\Resources\BusinessTypes\BusinessTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBusinessType extends ViewRecord
{
    protected static string $resource = BusinessTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
