<?php

namespace App\Filament\Resources\IndustryTypes;

use App\Filament\Resources\IndustryTypes\Pages\CreateIndustryType;
use App\Filament\Resources\IndustryTypes\Pages\EditIndustryType;
use App\Filament\Resources\IndustryTypes\Pages\ListIndustryTypes;
use App\Filament\Resources\IndustryTypes\Pages\ViewIndustryType;
use App\Filament\Resources\IndustryTypes\Schemas\IndustryTypeForm;
use App\Filament\Resources\IndustryTypes\Tables\IndustryTypesTable;
use App\Models\IndustryType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IndustryTypeResource extends Resource
{
    protected static ?string $model = IndustryType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bookmark;
    protected static string | UnitEnum | null $navigationGroup = 'Manage Types';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return IndustryTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndustryTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIndustryTypes::route('/'),
            'create' => CreateIndustryType::route('/create'),
            'edit' => EditIndustryType::route('/{record}/edit'),
            'view' => ViewIndustryType::route('/{record}')
        ];
    }
}
