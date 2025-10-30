<?php

namespace App\Filament\Resources\FiscalYearPeriods;

use App\Filament\Resources\FiscalYearPeriods\Pages\CreateFiscalYearPeriod;
use App\Filament\Resources\FiscalYearPeriods\Pages\EditFiscalYearPeriod;
use App\Filament\Resources\FiscalYearPeriods\Pages\ListFiscalYearPeriods;
use App\Filament\Resources\FiscalYearPeriods\Pages\ViewFiscalYearPeriod;
use App\Filament\Resources\FiscalYearPeriods\Schemas\FiscalYearPeriodForm;
use App\Filament\Resources\FiscalYearPeriods\Tables\FiscalYearPeriodsTable;
use App\Models\FiscalYearPeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FiscalYearPeriodResource extends Resource
{
    protected static ?string $model = FiscalYearPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bookmark;
    protected static string | UnitEnum | null $navigationGroup = 'Manage Types';
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FiscalYearPeriodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FiscalYearPeriodsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFiscalYearPeriods::route('/'),
            'create' => CreateFiscalYearPeriod::route('/create'),
            'edit' => EditFiscalYearPeriod::route('/{record}/edit'),
            'view' => ViewFiscalYearPeriod::route('/{record}')
        ];
    }
}
