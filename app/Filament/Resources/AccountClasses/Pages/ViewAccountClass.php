<?php

namespace App\Filament\Resources\AccountClasses\Pages;

use App\Filament\Resources\AccountClasses\AccountClassResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAccountClass extends ViewRecord
{
    protected static string $resource = AccountClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
