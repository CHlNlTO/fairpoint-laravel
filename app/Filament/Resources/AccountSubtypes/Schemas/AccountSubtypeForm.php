<?php

namespace App\Filament\Resources\AccountSubtypes\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Schemas\Components\Section;

class AccountSubtypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Subtype details and ownership rules.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('account_type_id')
                            ->label('Account Type')
                            ->relationship('accountType', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->inlineLabel()
                            ->default(true),
                        Forms\Components\Toggle::make('is_system_defined')
                            ->label('System defined')
                            ->inlineLabel()
                            ->default(true),
                    ]),
                Section::make('Additional')
                    ->description('Optional context and guidance.')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->rows(3),
                        Forms\Components\TextInput::make('hint')
                            ->label('Hint')
                            ->maxLength(200),
                    ])
                    ->compact(),
            ]);
    }
}
