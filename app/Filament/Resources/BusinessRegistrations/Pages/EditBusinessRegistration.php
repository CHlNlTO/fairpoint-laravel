<?php

namespace App\Filament\Resources\BusinessRegistrations\Pages;

use App\Filament\Resources\BusinessRegistrations\BusinessRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessRegistration extends EditRecord
{
    protected static string $resource = BusinessRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

