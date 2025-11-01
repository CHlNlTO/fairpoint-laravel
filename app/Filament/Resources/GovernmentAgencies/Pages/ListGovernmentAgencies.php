<?php

namespace App\Filament\Resources\GovernmentAgencies\Pages;

use App\Filament\Resources\GovernmentAgencies\GovernmentAgencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGovernmentAgencies extends ListRecords
{
    protected static string $resource = GovernmentAgencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
