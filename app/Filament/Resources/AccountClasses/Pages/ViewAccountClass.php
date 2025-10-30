<?php

namespace App\Filament\Resources\AccountClasses\Pages;

use App\Filament\Resources\AccountClasses\AccountClassResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Infolists;

class ViewAccountClass extends ViewRecord
{
    protected static string $resource = AccountClassResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Infolists\Components\TextEntry::make('code'),
            Infolists\Components\TextEntry::make('name')
                ->label('Name'),
            Infolists\Components\TextEntry::make('normal_balance')
                ->label('Normal Balance'),
            Infolists\Components\TextEntry::make('is_active')
                ->label('Active'),
        ]);
    }
}
