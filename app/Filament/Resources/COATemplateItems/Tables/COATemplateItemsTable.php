<?php

namespace App\Filament\Resources\COATemplateItems\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class COATemplateItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account_code')
                    ->label('Code')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('account_name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('accountSubtype.name')
                    ->label('Subtype')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('normal_balance')
                    ->label('Normal Balance')
                    ->colors([
                        'success' => 'debit',
                        'warning' => 'credit',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->badge()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('is_default')->label('Default'),
                SelectFilter::make('normal_balance')
                    ->label('Normal Balance')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                    ]),
                SelectFilter::make('account_subtype_id')
                    ->label('Account Subtype')
                    ->relationship('accountSubtype', 'name')
                    ->preload()
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
