<?php

namespace App\Filament\Resources\AccountSubtypes;

use App\Filament\Resources\AccountSubtypes\Pages\CreateAccountSubtype;
use App\Filament\Resources\AccountSubtypes\Pages\EditAccountSubtype;
use App\Filament\Resources\AccountSubtypes\Pages\ListAccountSubtypes;
use App\Filament\Resources\AccountSubtypes\Pages\ViewAccountSubtype;
use App\Filament\Resources\AccountSubtypes\Schemas\AccountSubtypeForm;
use App\Filament\Resources\AccountSubtypes\Tables\AccountSubtypesTable;
use App\Models\AccountSubtype;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AccountSubtypeResource extends Resource
{
    protected static ?string $model = AccountSubtype::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bookmark;
    protected static string | UnitEnum | null $navigationGroup = 'Manage Accounts';
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AccountSubtypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountSubtypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountSubtypes::route('/'),
            'create' => CreateAccountSubtype::route('/create'),
            'edit' => EditAccountSubtype::route('/{record}/edit'),
            'view' => ViewAccountSubtype::route('/{record}'),
        ];
    }
}
