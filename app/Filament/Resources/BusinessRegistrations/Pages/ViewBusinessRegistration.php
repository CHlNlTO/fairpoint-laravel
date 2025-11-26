<?php

namespace App\Filament\Resources\BusinessRegistrations\Pages;

use App\Filament\Resources\BusinessRegistrations\BusinessRegistrationResource;
use App\Filament\Resources\BusinessRegistrations\Schemas\BusinessRegistrationView;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewBusinessRegistration extends ViewRecord
{
    protected static string $resource = BusinessRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return BusinessRegistrationView::configure($schema);
    }
}
