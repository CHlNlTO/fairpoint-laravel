<?php

namespace App\Filament\Resources\AccountSubtypes\Pages;

use App\Filament\Resources\AccountSubtypes\AccountSubtypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountSubtype extends EditRecord
{
    protected static string $resource = AccountSubtypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
