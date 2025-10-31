<?php

namespace App\Filament\Resources\TaxCategories\Pages;

use App\Filament\Resources\TaxCategories\TaxCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTaxCategory extends ViewRecord
{
    protected static string $resource = TaxCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
