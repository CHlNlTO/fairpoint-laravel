<?php

namespace App\Filament\Resources\BusinessTypes;

use App\Filament\Resources\BusinessTypes\Pages\CreateBusinessType;
use App\Filament\Resources\BusinessTypes\Pages\EditBusinessType;
use App\Filament\Resources\BusinessTypes\Pages\ListBusinessTypes;
use App\Filament\Resources\BusinessTypes\Pages\ViewBusinessType;
use App\Filament\Resources\BusinessTypes\Schemas\BusinessTypeForm;
use App\Filament\Resources\BusinessTypes\Tables\BusinessTypesTable;
use App\Models\BusinessType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BusinessTypeResource extends Resource
{
    protected static ?string $model = BusinessType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bookmark;
    protected static string | UnitEnum | null $navigationGroup = 'Manage Types';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BusinessTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessTypes::route('/'),
            'create' => CreateBusinessType::route('/create'),
            'edit' => EditBusinessType::route('/{record}/edit'),
            'view' => ViewBusinessType::route('/{record}')
        ];
    }
}
