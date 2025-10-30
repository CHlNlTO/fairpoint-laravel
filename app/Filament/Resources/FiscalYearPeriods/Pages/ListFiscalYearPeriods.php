<?php

namespace App\Filament\Resources\FiscalYearPeriods\Pages;

use App\Filament\Resources\FiscalYearPeriods\FiscalYearPeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFiscalYearPeriods extends ListRecords
{
    protected static string $resource = FiscalYearPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
