<?php

namespace App\Filament\Resources\BusinessRegistrations\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class BusinessRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business Name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('tin_number')
                    ->label('TIN Number')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('business_email')
                    ->label('Business Email')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('fiscalYearPeriod.name')
                    ->label('Fiscal Year Period')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('businessType.name')
                    ->label('Business Type')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('region.name')
                    ->label('Region')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('province.name')
                    ->label('Province')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('City/Municipality')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('barangay.name')
                    ->label('Barangay')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('street_address')
                    ->label('Street Address')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->street_address)
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('building_name')
                    ->label('Building')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('unit_number')
                    ->label('Unit Number')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('postal_code')
                    ->label('Postal Code')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('industryTypes.name')
                    ->label('Industry Types')
                    ->badge()
                    ->separator(',')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('taxTypes.name')
                    ->label('Tax Types')
                    ->badge()
                    ->separator(',')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('governmentRegistrations.governmentAgency.name')
                    ->label('Government Agencies')
                    ->badge()
                    ->separator(',')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('business_type_id')
                    ->label('Business Type')
                    ->relationship('businessType', 'name'),
                SelectFilter::make('fiscal_year_period_id')
                    ->label('Fiscal Year Period')
                    ->relationship('fiscalYearPeriod', 'name'),
                SelectFilter::make('region_id')
                    ->label('Region')
                    ->relationship('region', 'name'),
                SelectFilter::make('province_id')
                    ->label('Province')
                    ->relationship('province', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->headerActions([
                //
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
