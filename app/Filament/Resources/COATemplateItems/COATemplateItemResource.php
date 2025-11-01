<?php

namespace App\Filament\Resources\COATemplateItems;

use App\Filament\Resources\COATemplateItems\Pages\CreateCOATemplateItem;
use App\Filament\Resources\COATemplateItems\Pages\EditCOATemplateItem;
use App\Filament\Resources\COATemplateItems\Pages\ListCOATemplateItems;
use App\Filament\Resources\COATemplateItems\Pages\ViewCOATemplateItem;
use App\Filament\Resources\COATemplateItems\Schemas\COATemplateItemForm;
use App\Filament\Resources\COATemplateItems\Tables\COATemplateItemsTable;
use App\Models\COATemplateItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class COATemplateItemResource extends Resource
{
    protected static ?string $model = COATemplateItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;
    protected static string | UnitEnum | null $navigationGroup = 'Manage Accounts';
    protected static ?int $navigationSort = 5;
    protected static ?string $recordTitleAttribute = 'account_name';

    protected static ?string $navigationLabel = 'Chart of Account Items';
    protected static ?string $pluralNavigationLabel = 'Chart of Account Items';
    protected static ?string $pluralModelLabel = 'Chart of Account Items';
    protected static ?string $modelLabel = 'Chart of Account Item';

    public static function form(Schema $schema): Schema
    {
        return COATemplateItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return COATemplateItemsTable::configure($table);
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
            'index' => ListCOATemplateItems::route('/'),
            'create' => CreateCOATemplateItem::route('/create'),
            'edit' => EditCOATemplateItem::route('/{record}/edit'),
            'view' => ViewCOATemplateItem::route('/{record}'),
        ];
    }
}
