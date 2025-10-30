<?php

namespace App\Filament\Resources\AccountClasses;

use App\Filament\Resources\AccountClasses\Pages\CreateAccountClass;
use App\Filament\Resources\AccountClasses\Pages\EditAccountClass;
use App\Filament\Resources\AccountClasses\Pages\ListAccountClasses;
use App\Filament\Resources\AccountClasses\Pages\ViewAccountClass;
use App\Filament\Resources\AccountClasses\Schemas\AccountClassForm;
use App\Filament\Resources\AccountClasses\Tables\AccountClassesTable;
use App\Models\AccountClass;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AccountClassResource extends Resource
{
    protected static ?string $model = AccountClass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bookmark;
    protected static string | UnitEnum | null $navigationGroup = 'Manage Accounts';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';


    public static function form(Schema $schema): Schema
    {
        return AccountClassForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountClassesTable::configure($table);
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
            'index' => ListAccountClasses::route('/'),
            'create' => CreateAccountClass::route('/create'),
            'edit' => EditAccountClass::route('/{record}/edit'),
            'view' => ViewAccountClass::route('/{record}'),
        ];
    }
}
