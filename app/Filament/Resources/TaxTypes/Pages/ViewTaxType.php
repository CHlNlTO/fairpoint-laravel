<?php

namespace App\Filament\Resources\TaxTypes\Pages;

use App\Filament\Resources\TaxTypes\TaxTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTaxType extends ViewRecord
{
    protected static string $resource = TaxTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
