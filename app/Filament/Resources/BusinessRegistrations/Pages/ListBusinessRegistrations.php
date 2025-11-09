<?php

namespace App\Filament\Resources\BusinessRegistrations\Pages;

use App\Filament\Resources\BusinessRegistrations\BusinessRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBusinessRegistrations extends ListRecords
{
    protected static string $resource = BusinessRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
