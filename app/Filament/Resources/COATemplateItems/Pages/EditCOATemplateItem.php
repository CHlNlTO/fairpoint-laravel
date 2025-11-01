<?php

namespace App\Filament\Resources\COATemplateItems\Pages;

use App\Filament\Resources\COATemplateItems\COATemplateItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCOATemplateItem extends EditRecord
{
    protected static string $resource = COATemplateItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
