<?php

namespace App\Filament\Resources\COATemplateItems\Pages;

use App\Filament\Resources\COATemplateItems\COATemplateItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCOATemplateItem extends ViewRecord
{
    protected static string $resource = COATemplateItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
