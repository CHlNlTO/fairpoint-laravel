<?php

namespace App\Filament\Resources\AccountClasses\Pages;

use App\Filament\Resources\AccountClasses\AccountClassResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountClass extends EditRecord
{
    protected static string $resource = AccountClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
