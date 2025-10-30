<?php

namespace App\Filament\Resources\AccountSubclasses;

use App\Filament\Resources\AccountSubclasses\Pages\CreateAccountSubclass;
use App\Filament\Resources\AccountSubclasses\Pages\EditAccountSubclass;
use App\Filament\Resources\AccountSubclasses\Pages\ListAccountSubclasses;
use App\Filament\Resources\AccountSubclasses\Pages\ViewAccountSubclass;
use App\Filament\Resources\AccountSubclasses\Schemas\AccountSubclassForm;
use App\Filament\Resources\AccountSubclasses\Tables\AccountSubclassesTable;
use App\Models\AccountSubclass;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AccountSubclassResource extends Resource
{
    protected static ?string $model = AccountSubclass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bookmark;
    protected static string | UnitEnum | null $navigationGroup = 'Manage Accounts';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';


    public static function form(Schema $schema): Schema
    {
        return AccountSubclassForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountSubclassesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountSubclasses::route('/'),
            'create' => CreateAccountSubclass::route('/create'),
            'edit' => EditAccountSubclass::route('/{record}/edit'),
            'view' => ViewAccountSubclass::route('/{record}'),
        ];
    }
}
