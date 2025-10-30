<?php

namespace App\Filament\Resources\AccountClasses\Pages;

use App\Filament\Resources\AccountClasses\AccountClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountClasses extends ListRecords
{
    protected static string $resource = AccountClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
