<?php

namespace App\Filament\Resources\GovernmentAgencies\Pages;

use App\Filament\Resources\GovernmentAgencies\GovernmentAgencyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGovernmentAgency extends EditRecord
{
    protected static string $resource = GovernmentAgencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
