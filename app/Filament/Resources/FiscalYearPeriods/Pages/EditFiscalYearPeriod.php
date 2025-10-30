<?php

namespace App\Filament\Resources\FiscalYearPeriods\Pages;

use App\Filament\Resources\FiscalYearPeriods\FiscalYearPeriodResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFiscalYearPeriod extends EditRecord
{
    protected static string $resource = FiscalYearPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
