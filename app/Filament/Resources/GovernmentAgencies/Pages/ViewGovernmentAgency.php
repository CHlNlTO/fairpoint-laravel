<?php

namespace App\Filament\Resources\GovernmentAgencies\Pages;

use App\Filament\Resources\GovernmentAgencies\GovernmentAgencyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGovernmentAgency extends ViewRecord
{
    protected static string $resource = GovernmentAgencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
