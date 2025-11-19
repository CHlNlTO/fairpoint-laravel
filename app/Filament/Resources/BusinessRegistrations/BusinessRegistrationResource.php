<?php

namespace App\Filament\Resources\BusinessRegistrations;

use App\Filament\Resources\BusinessRegistrations\Pages\CreateBusinessRegistration;
use App\Filament\Resources\BusinessRegistrations\Pages\EditBusinessRegistration;
use App\Filament\Resources\BusinessRegistrations\Pages\ListBusinessRegistrations;
use App\Filament\Resources\BusinessRegistrations\Pages\ViewBusinessRegistration;
use App\Filament\Resources\BusinessRegistrations\RelationManagers\BusinessCoaItemsRelationManager;
use App\Filament\Resources\BusinessRegistrations\Schemas\BusinessRegistrationForm;
use App\Filament\Resources\BusinessRegistrations\Tables\BusinessRegistrationsTable;
use App\Models\BusinessRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BusinessRegistrationResource extends Resource
{
    protected static ?string $model = BusinessRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice;
    protected static string | UnitEnum | null $navigationGroup = 'Manage Business';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'business_name';

    public static function form(Schema $schema): Schema
    {
        return BusinessRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessRegistrationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessRegistrations::route('/'),
            'create' => CreateBusinessRegistration::route('/create'),
            'edit' => EditBusinessRegistration::route('/{record}/edit'),
            'view' => ViewBusinessRegistration::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            BusinessCoaItemsRelationManager::class,
        ];
    }
}
