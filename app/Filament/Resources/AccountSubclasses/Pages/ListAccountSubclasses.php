<?php

namespace App\Filament\Resources\AccountSubclasses\Pages;

use App\Filament\Resources\AccountSubclasses\AccountSubclassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountSubclasses extends ListRecords
{
    protected static string $resource = AccountSubclassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
