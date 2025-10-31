<?php

namespace App\Filament\Resources\AccountSubtypes\Pages;

use App\Filament\Resources\AccountSubtypes\AccountSubtypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountSubtype extends ViewRecord
{
    protected static string $resource = AccountSubtypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
