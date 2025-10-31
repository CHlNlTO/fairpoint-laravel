<?php

namespace App\Filament\Resources\TaxCategories;

use App\Filament\Resources\TaxCategories\Pages\CreateTaxCategory;
use App\Filament\Resources\TaxCategories\Pages\EditTaxCategory;
use App\Filament\Resources\TaxCategories\Pages\ListTaxCategories;
use App\Filament\Resources\TaxCategories\Pages\ViewTaxCategory;
use App\Filament\Resources\TaxCategories\Schemas\TaxCategoryForm;
use App\Filament\Resources\TaxCategories\Tables\TaxCategoriesTable;
use App\Models\TaxCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TaxCategoryResource extends Resource
{
    protected static ?string $model = TaxCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bookmark;
    protected static string | UnitEnum | null $navigationGroup = 'Manage Types';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TaxCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxCategories::route('/'),
            'create' => CreateTaxCategory::route('/create'),
            'edit' => EditTaxCategory::route('/{record}/edit'),
            'view' => ViewTaxCategory::route('/{record}')
        ];
    }
}
