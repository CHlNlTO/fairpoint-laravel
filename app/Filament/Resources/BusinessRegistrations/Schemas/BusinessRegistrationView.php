<?php

namespace App\Filament\Resources\BusinessRegistrations\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;

class BusinessRegistrationView
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('business_name')
                            ->label('Business Name'),
                        TextEntry::make('tin_number')
                            ->label('TIN Number'),
                        TextEntry::make('business_email')
                            ->label('Business Email')
                            ->icon('heroicon-m-envelope'),
                        TextEntry::make('fiscalYearPeriod.name')
                            ->label('Fiscal Year Period'),
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                    ])
                    ->columns(2),

                Section::make('Business Classification')
                    ->schema([
                        TextEntry::make('businessType.name')
                            ->label('Business Type')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('industryTypes.name')
                            ->label('Industry Types')
                            ->badge()
                            ->separator(',')
                            ->placeholder('No industry types selected')
                            ->color('success'),
                        TextEntry::make('taxTypes.name')
                            ->label('Tax Types')
                            ->badge()
                            ->separator(',')
                            ->placeholder('No tax types selected')
                            ->color('warning')
                            ->visible(fn ($record) => $record->taxTypes->isNotEmpty()),
                    ])
                    ->columns(1)
                    ->compact(),

                Section::make('Address Information')
                    ->schema([
                        TextEntry::make('region.name')
                            ->label('Region'),
                        TextEntry::make('province.name')
                            ->label('Province'),
                        TextEntry::make('city.name')
                            ->label('City/Municipality'),
                        TextEntry::make('barangay.name')
                            ->label('Barangay'),
                        TextEntry::make('street_address')
                            ->label('Street Address')
                            ->columnSpanFull(),
                        TextEntry::make('building_name')
                            ->label('Building Name'),
                        TextEntry::make('unit_number')
                            ->label('Unit Number'),
                        TextEntry::make('postal_code')
                            ->label('Postal Code'),
                    ])
                    ->columns(2),

                Section::make('Government Agencies')
                    ->schema([
                        TextEntry::make('governmentRegistrations')
                            ->label('Agencies')
                            ->getStateUsing(function ($record) {
                                $agencies = $record->governmentRegistrations;
                                if ($agencies->isEmpty()) {
                                    return 'No government agencies registered';
                                }
                                return $agencies->pluck('governmentAgency.name')->filter()->join(', ');
                            })
                            ->placeholder('No government agencies registered'),
                    ])
                    ->compact(),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Registered By'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(3)
                    ->compact(),
            ]);
    }
}
