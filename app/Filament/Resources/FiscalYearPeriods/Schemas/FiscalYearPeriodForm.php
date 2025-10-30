<?php

namespace App\Filament\Resources\FiscalYearPeriods\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Schemas\Components\Section;

class FiscalYearPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Define the fiscal year start and end dates.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->inlineLabel()
                            ->default(true),
                        Forms\Components\TextInput::make('start_month')
                            ->label('Start Month')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(12),
                        Forms\Components\TextInput::make('start_day')
                            ->label('Start Day')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(31),
                        Forms\Components\TextInput::make('end_month')
                            ->label('End Month')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(12),
                        Forms\Components\TextInput::make('end_day')
                            ->label('End Day')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(31),
                        Forms\Components\Toggle::make('is_default')
                            ->label('Default')
                            ->inlineLabel()
                            ->default(false),
                    ]),
                Section::make('Additional')
                    ->description('Optional description for this fiscal year period.')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                    ])
                    ->compact(),
            ]);
    }
}
