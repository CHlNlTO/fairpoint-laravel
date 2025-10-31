<?php

namespace App\Filament\Resources\FiscalYearPeriods\Pages;

use App\Filament\Resources\FiscalYearPeriods\FiscalYearPeriodResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFiscalYearPeriod extends ViewRecord
{
    protected static string $resource = FiscalYearPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
