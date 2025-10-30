<?php

namespace App\Filament\Resources\AccountSubclasses\Pages;

use App\Filament\Resources\AccountSubclasses\AccountSubclassResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountSubclass extends ViewRecord
{
    protected static string $resource = AccountSubclassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
