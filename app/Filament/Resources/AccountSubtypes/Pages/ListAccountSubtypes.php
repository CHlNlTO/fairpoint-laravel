<?php

namespace App\Filament\Resources\AccountSubtypes\Pages;

use App\Filament\Resources\AccountSubtypes\AccountSubtypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountSubtypes extends ListRecords
{
    protected static string $resource = AccountSubtypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
