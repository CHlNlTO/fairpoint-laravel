<?php

namespace App\Filament\Resources\TaxTypes\Pages;

use App\Filament\Resources\TaxTypes\TaxTypeResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Infolists;

class ViewTaxType extends ViewRecord
{
    protected static string $resource = TaxTypeResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Infolists\Components\TextEntry::make('name')->label('Name'),
            Infolists\Components\TextEntry::make('is_active')->label('Active'),
            Infolists\Components\TextEntry::make('description')->label('Description'),
            Infolists\Components\TextEntry::make('hint')->label('Hint'),
        ]);
    }
}
