<?php

namespace App\Filament\Resources\COATemplateItems\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Schemas\Components\Section;

class COATemplateItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Core identifying details of the account item.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('account_code')
                            ->label('Account Code')
                            ->required()
                            ->length(6)
                            ->mask('999999')
                            ->unique(ignoreRecord: true)
                            ->helperText('6-digit account code'),
                        Forms\Components\TextInput::make('account_name')
                            ->label('Account Name')
                            ->required()
                            ->maxLength(200),
                        Forms\Components\Select::make('account_subtype_id')
                            ->label('Account Subtype')
                            ->relationship('accountSubtype', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('normal_balance')
                            ->label('Normal Balance')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Credit',
                            ])
                            ->required()
                            ->default('debit'),
                    ]),
                Section::make('Status')
                    ->description('Activation and default settings.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->inlineLabel()
                            ->default(true),
                        Forms\Components\Toggle::make('is_default')
                            ->label('Default')
                            ->inlineLabel()
                            ->default(false),
                    ])
                    ->compact(),
            ]);
    }
}
