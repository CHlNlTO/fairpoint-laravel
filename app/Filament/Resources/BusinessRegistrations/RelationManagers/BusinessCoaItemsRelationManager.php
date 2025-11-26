<?php

namespace App\Filament\Resources\BusinessRegistrations\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class BusinessCoaItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'coaItems';

    protected static ?string $title = 'Chart of Accounts';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('account_code')
            ->columns([
                Tables\Columns\TextColumn::make('account_code')
                    ->label('Account Code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Account Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_class')
                    ->label('Account Class')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_subclass')
                    ->label('Account Subclass')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_type')
                    ->label('Account Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_subtype')
                    ->label('Account Subtype')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('normal_balance')
                    ->label('Normal Balance')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'debit' => 'success',
                        'credit' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('account_code')
            ->filters([
                Tables\Filters\SelectFilter::make('normal_balance')
                    ->label('Normal Balance')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ]);
    }
}
