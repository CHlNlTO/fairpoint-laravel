<?php

namespace App\Filament\Resources\AccountSubclasses\Pages;

use App\Filament\Resources\AccountSubclasses\AccountSubclassResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountSubclass extends EditRecord
{
    protected static string $resource = AccountSubclassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
