<?php

namespace App\Filament\Resources\GovernmentAgencies;

use App\Filament\Resources\GovernmentAgencies\Pages\CreateGovernmentAgency;
use App\Filament\Resources\GovernmentAgencies\Pages\EditGovernmentAgency;
use App\Filament\Resources\GovernmentAgencies\Pages\ListGovernmentAgencies;
use App\Filament\Resources\GovernmentAgencies\Pages\ViewGovernmentAgency;
use App\Filament\Resources\GovernmentAgencies\Schemas\GovernmentAgencyForm;
use App\Filament\Resources\GovernmentAgencies\Tables\GovernmentAgenciesTable;
use App\Models\GovernmentAgency;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GovernmentAgencyResource extends Resource
{
    protected static ?string $model = GovernmentAgency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingLibrary;
    protected static string | UnitEnum | null $navigationGroup = 'Manage Agencies';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';


    public static function form(Schema $schema): Schema
    {
        return GovernmentAgencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GovernmentAgenciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGovernmentAgencies::route('/'),
            'create' => CreateGovernmentAgency::route('/create'),
            'edit' => EditGovernmentAgency::route('/{record}/edit'),
            'view' => ViewGovernmentAgency::route('/{record}'),
        ];
    }
}
