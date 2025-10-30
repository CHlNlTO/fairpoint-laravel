<?php

namespace App\Filament\Resources\AccountTypes\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Schemas\Components\Section;

class AccountTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Type details and ownership rules.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('account_subclass_id')
                            ->label('Account Subclass')
                            ->relationship('accountSubclass', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(99),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->inlineLabel()
                            ->default(true),
                        Forms\Components\Toggle::make('is_system_defined')
                            ->label('System defined')
                            ->inlineLabel()
                            ->default(true),
                    ]),
                // Section::make('Ownership & Notes')
                //     ->description('Optional associations and descriptions.')
                //     ->schema([
                //         Forms\Components\Select::make('user_id')
                //             ->label('User')
                //             ->relationship('user', 'name') // adjust display field if different
                //             ->searchable()
                //             ->preload()
                //             ->visible(fn (Forms\Get $get) => $get('is_system_defined') === false)
                //             ->nullable(),
                //         Forms\Components\Select::make('business_registration_id')
                //             ->label('Business Registration')
                //             ->relationship('businessRegistration', 'name') // adjust display
                //             ->searchable()
                //             ->preload()
                //             ->visible(fn (Forms\Get $get) => $get('is_system_defined') === false)
                //             ->nullable(),
                //         Forms\Components\Textarea::make('description')
                //             ->label('Description')
                //             ->maxLength(500)
                //             ->rows(3),
                //         Forms\Components\TextInput::make('hint')
                //             ->label('Hint')
                //             ->maxLength(200),
                //     ])
                //     ->compact(),
            ]);
    }
}
