<?php

namespace App\Filament\Resources\AccountClasses\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Schemas\Components\Section;

class AccountClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Core identifying details of the account class.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Code')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Select::make('normal_balance')
                            ->label('Normal Balance')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Credit',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->inlineLabel()
                            ->default(true),
                    ]),
                Section::make('Additional')
                    ->description('Optional context and short hint shown elsewhere.')
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
