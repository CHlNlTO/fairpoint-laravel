<?php

namespace App\Filament\Resources\BusinessRegistrations\Schemas;

use App\Models\BusinessType;
use App\Models\FiscalYearPeriod;
use App\Models\GovernmentAgency;
use App\Models\IndustryType;
use App\Models\TaxCategory;
use App\Models\TaxType;
use App\Models\COATemplateItem;
use App\Models\COAItemBusinessType;
use App\Models\COAItemTaxType;
use App\Models\COAItemIndustryType;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Section;
use Yajra\Address\Entities\Region;
use Yajra\Address\Entities\Province;
use Yajra\Address\Entities\City;
use Yajra\Address\Entities\Barangay;

class BusinessRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Basic Information')
                        ->schema([
                            Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('business_name')
                                        ->label('Name of Business')
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('tin_number')
                                        ->label('TIN')
                                        ->placeholder('XXX-XXX-XXX-XXX')
                                        ->required()
                                        ->mask('999-999-999-999')
                                        ->maxLength(15),
                                    Forms\Components\TextInput::make('business_email')
                                        ->label('Business Email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->columns(4),
                            Section::make('Address')
                                ->schema([
                                    Forms\Components\Select::make('region_id')
                                        ->label('Region')
                                        ->options(function () {
                                            return Region::query()
                                                ->orderBy('name')
                                                ->pluck('name', 'id');
                                        })
                                        ->searchable()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set) {
                                            $set('province_id', null);
                                            $set('city_id', null);
                                            $set('barangay_id', null);
                                        }),
                                    Forms\Components\Select::make('province_id')
                                        ->label('Province')
                                        ->options(function (Get $get) {
                                            $regionId = $get('region_id');
                                            if (!$regionId) {
                                                return [];
                                            }
                                            // Get the region's region_id value to filter provinces
                                            $region = Region::find($regionId);
                                            if (!$region) {
                                                return [];
                                            }
                                            return Province::query()
                                                ->where('region_id', $region->region_id)
                                                ->orderBy('name')
                                                ->pluck('name', 'id');
                                        })
                                        ->searchable()
                                        ->required()
                                        ->live()
                                        ->disabled(fn (Get $get) => !$get('region_id'))
                                        ->afterStateUpdated(function (Set $set) {
                                            $set('city_id', null);
                                            $set('barangay_id', null);
                                        }),
                                    Forms\Components\Select::make('city_id')
                                        ->label('City/Municipality')
                                        ->options(function (Get $get) {
                                            $regionId = $get('region_id');
                                            $provinceId = $get('province_id');
                                            if (!$regionId || !$provinceId) {
                                                return [];
                                            }
                                            // Get the region's and province's region_id/province_id values to filter cities
                                            $region = Region::find($regionId);
                                            $province = Province::find($provinceId);
                                            if (!$region || !$province) {
                                                return [];
                                            }
                                            return City::query()
                                                ->where('region_id', $region->region_id)
                                                ->where('province_id', $province->province_id)
                                                ->orderBy('name')
                                                ->pluck('name', 'id');
                                        })
                                        ->searchable()
                                        ->required()
                                        ->live()
                                        ->disabled(fn (Get $get) => !$get('province_id'))
                                        ->afterStateUpdated(function (Set $set) {
                                            $set('barangay_id', null);
                                        }),
                                    Forms\Components\Select::make('barangay_id')
                                        ->label('Barangay')
                                        ->options(function (Get $get) {
                                            $regionId = $get('region_id');
                                            $provinceId = $get('province_id');
                                            $cityId = $get('city_id');
                                            if (!$regionId || !$provinceId || !$cityId) {
                                                return [];
                                            }
                                            // Get the region's, province's, and city's region_id/province_id/city_id values to filter barangays
                                            $region = Region::find($regionId);
                                            $province = Province::find($provinceId);
                                            $city = City::find($cityId);
                                            if (!$region || !$province || !$city) {
                                                return [];
                                            }
                                            return Barangay::query()
                                                ->where('region_id', $region->region_id)
                                                ->where('province_id', $province->province_id)
                                                ->where('city_id', $city->city_id)
                                                ->orderBy('name')
                                                ->pluck('name', 'id');
                                        })
                                        ->searchable()
                                        ->required()
                                        ->disabled(fn (Get $get) => !$get('city_id')),
                                    Forms\Components\TextInput::make('street_address')
                                        ->label('Street Address')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('building_name')
                                        ->label('Building Name')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('unit_number')
                                        ->label('Unit Number')
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('postal_code')
                                        ->label('Postal Code')
                                        ->numeric()
                                        ->length(4)
                                        ->regex('/^\d{4}$/')
                                        ->dehydrateStateUsing(fn ($state) => $state ? (string) $state : null)
                                        ->validationMessages([
                                            'regex' => 'Postal code must be exactly 4 digits.',
                                        ]),
                                ])
                                ->columns(4),
                        ]),

                    Wizard\Step::make('Industry Types')
                        ->schema([
                            Forms\Components\CheckboxList::make('industry_type_ids')
                                ->label('Select Industry Types')
                                ->options(function () {
                                    return IndustryType::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->required()
                                ->live()
                                ->columns(2)
                                ->gridDirection('row')
                                ->bulkToggleable(),
                        ]),

                    Wizard\Step::make('Fiscal Year Period')
                        ->schema([
                            Forms\Components\Select::make('fiscal_year_period_id')
                                ->label('Fiscal Year Period')
                                ->options(function () {
                                    return FiscalYearPeriod::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->required(),
                        ]),

                    Wizard\Step::make('Business Type')
                        ->schema([
                            Forms\Components\Radio::make('business_type_id')
                                ->label('Select Business Type')
                                ->options(function () {
                                    return BusinessType::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->required()
                                ->live()
                                ->columns(2)
                                ->descriptions(function () {
                                    $types = BusinessType::query()
                                        ->where('is_active', true)
                                        ->get();
                                    return $types->mapWithKeys(function ($type) {
                                        return [$type->id => $type->description ?? ''];
                                    })->toArray();
                                }),
                        ]),

                    Wizard\Step::make('Government Agencies')
                        ->schema([
                            Forms\Components\CheckboxList::make('government_agency_ids')
                                ->label('Select Government Agencies')
                                ->options(function () {
                                    return GovernmentAgency::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->required()
                                ->live()
                                ->columns(2)
                                ->gridDirection('row')
                                ->bulkToggleable(),
                        ]),

                    Wizard\Step::make('Tax Types')
                        ->schema(function (Get $get) {
                            $governmentAgencyIds = $get('government_agency_ids') ?? [];

                            // Check if BIR is selected
                            $birAgency = GovernmentAgency::where('code', 'BIR')
                                ->where('is_active', true)
                                ->first();

                            if (!$birAgency || !in_array($birAgency->id, $governmentAgencyIds)) {
                                return [];
                            }

                            // Get all tax categories
                            $categories = TaxCategory::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get();

                            $components = [];

                            foreach ($categories as $category) {
                                $taxTypes = TaxType::query()
                                    ->where('category_id', $category->id)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');

                                if ($taxTypes->isEmpty()) {
                                    continue;
                                }

                                // If category name is "Additional Tax", allow multiple selection
                                if (strtolower($category->name) === 'additional tax') {
                                    $components[] = Forms\Components\CheckboxList::make("tax_type_ids_{$category->id}")
                                        ->label($category->name)
                                        ->options($taxTypes->toArray())
                                        ->live()
                                        ->columns(2)
                                        ->gridDirection('row')
                                        ->bulkToggleable();
                                } else {
                                    $components[] = Forms\Components\Select::make("tax_type_id_{$category->id}")
                                        ->label($category->name)
                                        ->options($taxTypes->toArray())
                                        ->searchable()
                                        ->live();
                                }
                            }

                            return $components;
                        })
                        ->visible(function (Get $get) {
                            $governmentAgencyIds = $get('government_agency_ids') ?? [];
                            $birAgency = GovernmentAgency::where('code', 'BIR')
                                ->where('is_active', true)
                                ->first();

                            return $birAgency && in_array($birAgency->id, $governmentAgencyIds);
                        }),

                    Wizard\Step::make('Chart of Accounts')
                        ->schema([
                            TextEntry::make('coa_info')
                                ->label('')
                                ->state('The following Chart of Accounts will be automatically assigned based on your selections.')
                                ->columnSpanFull(),
                            Repeater::make('coa_template_items_display')
                                ->label('Chart of Accounts')
                                ->table([
                                    TableColumn::make('account_code')
                                        ->width('120px'),
                                    TableColumn::make('account_name'),
                                    TableColumn::make('account_subtype'),
                                    TableColumn::make('normal_balance')
                                        ->width('120px'),
                                ])
                                ->compact()
                                ->schema([
                                    Forms\Components\TextInput::make('account_code')
                                        ->hiddenLabel()
                                        ->disabled()
                                        ->dehydrated(false),
                                    Forms\Components\TextInput::make('account_name')
                                        ->hiddenLabel()
                                        ->disabled()
                                        ->dehydrated(false),
                                    Forms\Components\TextInput::make('account_subtype')
                                        ->hiddenLabel()
                                        ->disabled()
                                        ->dehydrated(false),
                                    Forms\Components\TextInput::make('normal_balance')
                                        ->hiddenLabel()
                                        ->disabled()
                                        ->dehydrated(false),
                                ])
                                ->defaultItems(0)
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->itemLabel(fn (array $state): ?string => $state['account_code'] ?? null)
                                ->live(onBlur: false)
                                ->default(function (Get $get) {
                                    // Get selected values from previous steps
                                    $businessTypeId = $get('business_type_id');
                                    $industryTypeIds = $get('industry_type_ids') ?? [];
                                    $taxTypeIds = [];

                                    // Collect tax type IDs from all categories
                                    $birAgency = GovernmentAgency::where('code', 'BIR')
                                        ->where('is_active', true)
                                        ->first();

                                    if ($birAgency && in_array($birAgency->id, $get('government_agency_ids') ?? [])) {
                                        $categories = TaxCategory::query()
                                            ->where('is_active', true)
                                            ->get();

                                        foreach ($categories as $category) {
                                            if (strtolower($category->name) === 'additional tax') {
                                                $categoryTaxIds = $get("tax_type_ids_{$category->id}") ?? [];
                                                $taxTypeIds = array_merge($taxTypeIds, $categoryTaxIds);
                                            } else {
                                                $categoryTaxId = $get("tax_type_id_{$category->id}");
                                                if ($categoryTaxId) {
                                                    $taxTypeIds[] = $categoryTaxId;
                                                }
                                            }
                                        }
                                    }

                                    // Collect all COA item IDs
                                    $coaItemIds = [];

                                    // Get default COA items
                                    $defaultCoaIds = COATemplateItem::query()
                                        ->where('is_default', true)
                                        ->where('is_active', true)
                                        ->pluck('id')
                                        ->toArray();
                                    $coaItemIds = array_merge($coaItemIds, $defaultCoaIds);

                                    // Get COA items by business type
                                    if ($businessTypeId) {
                                        $businessTypeCoaIds = COAItemBusinessType::query()
                                            ->where('business_type_id', $businessTypeId)
                                            ->pluck('account_item_id')
                                            ->toArray();
                                        $coaItemIds = array_merge($coaItemIds, $businessTypeCoaIds);
                                    }

                                    // Get COA items by industry types
                                    if (!empty($industryTypeIds)) {
                                        $industryTypeCoaIds = COAItemIndustryType::query()
                                            ->whereIn('industry_type_id', $industryTypeIds)
                                            ->pluck('account_item_id')
                                            ->toArray();
                                        $coaItemIds = array_merge($coaItemIds, $industryTypeCoaIds);
                                    }

                                    // Get COA items by tax types
                                    if (!empty($taxTypeIds)) {
                                        $taxTypeCoaIds = COAItemTaxType::query()
                                            ->whereIn('tax_type_id', $taxTypeIds)
                                            ->pluck('account_item_id')
                                            ->toArray();
                                        $coaItemIds = array_merge($coaItemIds, $taxTypeCoaIds);
                                    }

                                    // Remove duplicates and get unique items
                                    $coaItemIds = array_unique($coaItemIds);

                                    if (empty($coaItemIds)) {
                                        return [];
                                    }

                                    // Get the COA items with relationships
                                    $coaItems = COATemplateItem::query()
                                        ->whereIn('id', $coaItemIds)
                                        ->where('is_active', true)
                                        ->with('accountSubtype.accountType.accountSubclass.accountClass')
                                        ->orderBy('account_code')
                                        ->get();

                                    // Format the data for the repeater
                                    return $coaItems->map(function ($item) {
                                        return [
                                            'account_code' => $item->account_code,
                                            'account_name' => $item->account_name,
                                            'account_subtype' => $item->accountSubtype?->name ?? 'N/A',
                                            'normal_balance' => ucfirst($item->normal_balance ?? 'N/A'),
                                        ];
                                    })->toArray();
                                })
                                ->columns(4)
                                ->collapsed()
                                ->hiddenLabel(),
                        ]),
                ])
                ->columnSpanFull()
            ]);
    }
}
