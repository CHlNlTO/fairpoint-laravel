<?php

namespace App\Filament\Resources\FiscalYearPeriods\Pages;

use App\Filament\Resources\FiscalYearPeriods\FiscalYearPeriodResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Infolists;

class ViewFiscalYearPeriod extends ViewRecord
{
    protected static string $resource = FiscalYearPeriodResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Infolists\Components\TextEntry::make('name')->label('Name'),
            Infolists\Components\TextEntry::make('start_month')->label('Start Month'),
            Infolists\Components\TextEntry::make('start_day')->label('Start Day'),
            Infolists\Components\TextEntry::make('end_month')->label('End Month'),
            Infolists\Components\TextEntry::make('end_day')->label('End Day'),
            Infolists\Components\TextEntry::make('is_default')->label('Default'),
            Infolists\Components\TextEntry::make('is_active')->label('Active'),
            Infolists\Components\TextEntry::make('description')->label('Description'),
        ]);
    }
}
