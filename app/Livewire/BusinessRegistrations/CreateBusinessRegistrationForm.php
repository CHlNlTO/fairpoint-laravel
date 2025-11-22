<?php

namespace App\Livewire\BusinessRegistrations;

use App\Models\AccountClass;
use App\Models\AccountSubclass;
use App\Models\AccountSubtype;
use App\Models\AccountType;
use App\Models\BusinessCoaItem;
use App\Models\BusinessGovernmentRegistration;
use App\Models\BusinessRegistration;
use App\Models\BusinessType;
use App\Models\COAItemBusinessType;
use App\Models\COAItemIndustryType;
use App\Models\COAItemTaxType;
use App\Models\FiscalYearPeriod;
use App\Models\GovernmentAgency;
use App\Models\IndustryType;
use App\Models\TaxCategory;
use App\Models\TaxType;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Yajra\Address\Entities\Barangay;
use Yajra\Address\Entities\City;
use Yajra\Address\Entities\Province;
use Yajra\Address\Entities\Region;

class CreateBusinessRegistrationForm extends Component
{
    public int $currentStep = 1;

    public string $business_name = '';
    public string $tin_number = '';
    public string $business_email = '';

    public ?string $region_id = null;
    public ?string $province_id = null;
    public ?string $city_id = null;
    public ?string $barangay_id = null;

    public ?string $street_address = null;
    public ?string $building_name = null;
    public ?string $unit_number = null;
    public ?string $postal_code = null;

    /** @var array<string> */
    public array $industry_type_ids = [];

    public ?string $fiscal_year_period_id = null;
    public ?string $business_type_id = null;

    /** @var array<string> */
    public array $government_agency_ids = [];

    /** @var array<string, mixed> */
    public array $taxSelections = [];

    public array $regions = [];
    public array $provinces = [];
    public array $cities = [];
    public array $barangays = [];
    public array $industryTypes = [];
    public array $fiscalYearPeriods = [];
    public array $businessTypes = [];
    public array $governmentAgencies = [];
    public array $taxCategories = [];
    public array $coaPreview = [];
    // Public but not synced - these are large lookup arrays only used for client-side filtering
    public array $regionLookup = [];
    public array $provinceLookup = [];
    public array $cityLookup = [];
    public array $barangayLookup = [];

    // COA Template Data for client-side filtering
    public array $allCoaItems = [];
    public array $coaItemsByBusinessType = [];
    public array $coaItemsByIndustryType = [];
    public array $coaItemsByTaxType = [];
    public array $coaStructure = [
        'classCodes' => [],
        'subclassOrders' => [],
        'typeOrders' => [],
        'subtypeOrders' => [],
    ];
    public array $selectedCoaItems = [];

    // Hierarchy data for COA item creation
    public array $accountClasses = [];
    public array $accountSubclasses = [];
    public array $accountTypes = [];
    public array $accountSubtypes = [];

    public ?string $birAgencyId = null;

    public function mount(): void
    {
        $this->loadStaticOptions();
        $this->initializeTaxSelections();

        // OPTIMIZED: Load COA data upfront so it's cached and ready
        $this->loadCoaTemplateData();
        $this->updateCoaPreview();
    }

    public function updatedRegionId($value): void
    {
        $this->region_id = $value ?: null;
        $this->province_id = null;
        $this->city_id = null;
        $this->barangay_id = null;

        $this->loadProvinces();
        $this->cities = [];
        $this->barangays = [];
    }

    public function updatedProvinceId($value): void
    {
        $this->province_id = $value ?: null;
        $this->city_id = null;
        $this->barangay_id = null;

        $this->loadCities();
        $this->barangays = [];
    }

    public function updatedCityId($value): void
    {
        $this->city_id = $value ?: null;
        $this->barangay_id = null;

        $this->loadBarangays();
    }

    public function updatedBarangayId($value): void
    {
        $this->barangay_id = $value ?: null;
    }

    public function updatedIndustryTypeIds($value): void
    {
        $ids = array_filter((array) $this->industry_type_ids, fn ($id) => filled($id));
        $this->industry_type_ids = array_values(array_unique(array_map('strval', $ids)));
        $this->updateCoaPreview();
    }

    public function updatedFiscalYearPeriodId($value): void
    {
        $this->fiscal_year_period_id = $value ?: null;
    }

    public function updatedBusinessTypeId($value): void
    {
        $this->business_type_id = $value ?: null;
        $this->updateCoaPreview();
    }

    public function updatedGovernmentAgencyIds($value): void
    {
        $ids = array_filter((array) $this->government_agency_ids, fn ($id) => filled($id));
        $this->government_agency_ids = array_values(array_unique(array_map('strval', $ids)));

        if (! $this->isBirSelected()) {
            $this->initializeTaxSelections(clearOnly: true);
        }

        $this->updateCoaPreview();
    }

    public function updatedTaxSelections($value, ?string $key = null): void
    {
        if (! $this->isBirSelected()) {
            $this->initializeTaxSelections(clearOnly: true);
            $this->updateCoaPreview();

            return;
        }

        $this->normalizeTaxSelections();
        $this->updateCoaPreview();
    }

    public function nextStep(): void
    {
        $this->validate($this->getStepRules($this->currentStep));

        if ($this->currentStep < 5) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function submit(): void
    {
        try {
            $this->validate($this->getAllRules());

            // OPTIMIZED: Single transaction with batch inserts
            DB::transaction(function () {
                $now = now();
                $userId = Auth::id();

                $data = [
                    'business_name' => $this->business_name,
                    'tin_number' => $this->tin_number,
                    'business_email' => $this->business_email,
                    'region_id' => $this->region_id,
                    'province_id' => $this->province_id,
                    'city_id' => $this->city_id,
                    'barangay_id' => $this->barangay_id,
                    'street_address' => $this->street_address ?: null,
                    'building_name' => $this->building_name ?: null,
                    'unit_number' => $this->unit_number ?: null,
                    'postal_code' => $this->sanitizePostalCode($this->postal_code),
                    'fiscal_year_period_id' => $this->fiscal_year_period_id,
                    'business_type_id' => $this->business_type_id,
                    'is_active' => true,
                    'user_id' => $userId,
                ];

                /** @var BusinessRegistration $businessRegistration */
                $businessRegistration = BusinessRegistration::create($data);
                $businessId = $businessRegistration->id;

                // OPTIMIZED: Batch insert for industry types (faster than sync)
                if (! empty($this->industry_type_ids)) {
                    $industryData = array_map(function ($industryId) use ($businessId, $now) {
                        return [
                            'business_registration_id' => $businessId,
                            'industry_type_id' => $industryId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }, $this->industry_type_ids);
                    DB::table('business_registration_industry_types')->insert($industryData);
                }

                // OPTIMIZED: Batch insert for tax types (faster than sync)
                $taxTypeIds = $this->collectSelectedTaxTypeIds();
                if (! empty($taxTypeIds)) {
                    $taxData = array_map(function ($taxId) use ($businessId, $now) {
                        return [
                            'business_registration_id' => $businessId,
                            'tax_type_id' => $taxId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }, $taxTypeIds);
                    DB::table('business_registration_tax_types')->insert($taxData);
                }

                // OPTIMIZED: Batch insert for government registrations
                if (! empty($this->government_agency_ids)) {
                    $agencyData = array_map(function ($agencyId) use ($businessId, $now) {
                        return [
                            'id' => (string) Str::uuid(),
                            'business_registration_id' => $businessId,
                            'government_agency_id' => $agencyId,
                            'is_active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }, $this->government_agency_ids);
                    DB::table('business_government_registrations')->insert($agencyData);
                }

                if (! empty($this->selectedCoaItems)) {
                    $coaItems = collect($this->selectedCoaItems)
                        ->filter(fn ($item) => filled($item['coa_item_id'] ?? null) && filled($item['account_code'] ?? null))
                        ->map(function ($item) use ($businessId, $now) {
                            $normalBalance = strtolower($item['normal_balance'] ?? 'debit');
                            if (! in_array($normalBalance, ['debit', 'credit'], true)) {
                                $normalBalance = 'debit';
                            }

                            return [
                                'id' => (string) Str::uuid(),
                                'business_id' => $businessId,
                                'coa_item_id' => $item['coa_item_id'],
                                'account_code' => $item['account_code'],
                                'account_class' => $item['account_class'] ?? 'N/A',
                                'account_subclass' => $item['account_subclass'] ?? 'N/A',
                                'account_type' => $item['account_type'] ?? 'N/A',
                                'account_subtype' => $item['account_subtype'] ?? 'N/A',
                                'normal_balance' => $normalBalance,
                                'is_active' => (bool) ($item['is_active'] ?? true),
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        })
                        ->values();

                    if ($coaItems->isNotEmpty()) {
                        BusinessCoaItem::insert($coaItems->toArray());
                    }
                }
            });

            Notification::make()
                ->success()
                ->title('Business registration created')
                ->body('The business registration record is now available in the listings.')
                ->send();

            $this->redirectRoute('filament.app.resources.business-registrations.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Business registration submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->danger()
                ->title('Error creating business registration')
                ->body('An error occurred while creating the business registration. Please try again.')
                ->send();
        }
    }

    public function getCoaPreview(): array
    {
        $this->updateCoaPreview();
        return $this->coaPreview;
    }

    public function loadCoaData(): array
    {
        // Lazy load COA data only when needed
        if (empty($this->allCoaItems)) {
            $this->loadCoaTemplateData();
        }

        return [
            'allCoaItems' => $this->allCoaItems,
            'coaItemsByBusinessType' => $this->coaItemsByBusinessType,
            'coaItemsByIndustryType' => $this->coaItemsByIndustryType,
            'coaItemsByTaxType' => $this->coaItemsByTaxType,
            'coaStructure' => $this->coaStructure,
            'selectedCoaItems' => $this->selectedCoaItems,
        ];
    }

    public function render()
    {
        return view('livewire.business-registrations.create-business-registration-form');
    }

    protected function loadStaticOptions(): void
    {
        $regionCollection = Region::query()
            ->orderBy('name')
            ->get();

        $this->regions = $regionCollection
            ->mapWithKeys(fn (Region $region) => [(string) $region->id => $region->name])
            ->toArray();

        $this->regionLookup = $regionCollection
            ->mapWithKeys(fn (Region $region) => [
                (string) $region->id => [
                    'psgc' => (string) $region->region_id,
                    'name' => $region->name,
                ],
            ])
            ->toArray();

        $provinceCollection = Province::query()
            ->orderBy('name')
            ->get();

        $this->provinceLookup = $provinceCollection
            ->mapWithKeys(fn (Province $province) => [
                (string) $province->id => [
                    'name' => $province->name,
                    'region_psgc' => (string) $province->region_id,
                    'psgc' => (string) $province->province_id,
                ],
            ])
            ->toArray();

        $cityCollection = City::query()
            ->orderBy('name')
            ->get();

        $this->cityLookup = $cityCollection
            ->mapWithKeys(fn (City $city) => [
                (string) $city->id => [
                    'name' => $city->name,
                    'region_psgc' => (string) $city->region_id,
                    'province_psgc' => (string) $city->province_id,
                    'psgc' => (string) $city->city_id,
                ],
            ])
            ->toArray();

        $barangayCollection = Barangay::query()
            ->orderBy('name')
            ->get();

        $this->barangayLookup = $barangayCollection
            ->mapWithKeys(fn (Barangay $barangay) => [
                (string) $barangay->id => [
                    'name' => $barangay->name,
                    'region_psgc' => (string) $barangay->region_id,
                    'province_psgc' => (string) $barangay->province_id,
                    'city_psgc' => (string) $barangay->city_id,
                ],
            ])
            ->toArray();

        $this->loadProvinces();
        $this->loadCities();
        $this->loadBarangays();

        $this->industryTypes = IndustryType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->fiscalYearPeriods = FiscalYearPeriod::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->businessTypes = BusinessType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->governmentAgencies = GovernmentAgency::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->mapWithKeys(function (GovernmentAgency $agency) {
                return [$agency->id => [
                    'name' => $agency->name,
                    'code' => $agency->code,
                ]];
            })
            ->toArray();

        $this->birAgencyId = null;
        foreach ($this->governmentAgencies as $id => $agency) {
            if (($agency['code'] ?? null) === 'BIR') {
                $this->birAgencyId = (string) $id;
                break;
            }
        }

        $this->taxCategories = TaxCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (TaxCategory $category) {
                $taxTypes = TaxType::query()
                    ->where('category_id', $category->id)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray();

                return [
                    $category->id => [
                        'name' => $category->name,
                        'is_additional' => mb_strtolower($category->name) === 'additional tax',
                        'tax_types' => $taxTypes,
                    ],
                ];
            })
            ->toArray();
    }

    protected function initializeTaxSelections(bool $clearOnly = false): void
    {
        foreach ($this->taxCategories as $categoryId => $meta) {
            if ($clearOnly) {
                $this->taxSelections[$categoryId] = $meta['is_additional'] ? [] : null;
                continue;
            }

            if (! array_key_exists($categoryId, $this->taxSelections)) {
                $this->taxSelections[$categoryId] = $meta['is_additional'] ? [] : null;
            }
        }
    }

    protected function normalizeTaxSelections(): void
    {
        foreach ($this->taxCategories as $categoryId => $meta) {
            if ($meta['is_additional']) {
                $values = array_filter((array) ($this->taxSelections[$categoryId] ?? []), fn ($value) => filled($value));
                $this->taxSelections[$categoryId] = array_values(array_unique(array_map('strval', $values)));
            } else {
                $selection = $this->taxSelections[$categoryId] ?? null;
                $this->taxSelections[$categoryId] = filled($selection) ? (string) $selection : null;
            }
        }
    }

    protected function loadProvinces(): void
    {
        $this->provinces = [];

        if (! $this->region_id) {
            return;
        }

        $region = $this->regionLookup[$this->region_id] ?? null;

        if (! $region) {
            return;
        }

        $regionPsgc = $region['psgc'] ?? null;

        if (! $regionPsgc) {
            return;
        }

        $this->provinces = collect($this->provinceLookup)
            ->filter(fn (array $province) => $province['region_psgc'] === $regionPsgc)
            ->mapWithKeys(fn (array $province, string $id) => [$id => $province['name']])
            ->toArray();
    }

    protected function loadCities(): void
    {
        $this->cities = [];

        if (! $this->region_id || ! $this->province_id) {
            return;
        }

        $region = $this->regionLookup[$this->region_id] ?? null;
        $province = $this->provinceLookup[$this->province_id] ?? null;

        if (! $region || ! $province) {
            return;
        }

        $regionPsgc = $region['psgc'] ?? null;
        $provincePsgc = $province['psgc'] ?? null;

        if (! $regionPsgc || ! $provincePsgc) {
            return;
        }

        $this->cities = collect($this->cityLookup)
            ->filter(function (array $city) use ($regionPsgc, $provincePsgc) {
                return $city['region_psgc'] === $regionPsgc
                    && $city['province_psgc'] === $provincePsgc;
            })
            ->mapWithKeys(fn (array $city, string $id) => [$id => $city['name']])
            ->toArray();
    }

    protected function loadBarangays(): void
    {
        $this->barangays = [];

        if (! $this->region_id || ! $this->province_id || ! $this->city_id) {
            return;
        }

        $region = $this->regionLookup[$this->region_id] ?? null;
        $province = $this->provinceLookup[$this->province_id] ?? null;
        $city = $this->cityLookup[$this->city_id] ?? null;

        if (! $region || ! $province || ! $city) {
            return;
        }

        $regionPsgc = $region['psgc'] ?? null;
        $provincePsgc = $province['psgc'] ?? null;
        $cityPsgc = $city['psgc'] ?? null;

        if (! $regionPsgc || ! $provincePsgc || ! $cityPsgc) {
            return;
        }

        $this->barangays = collect($this->barangayLookup)
            ->filter(function (array $barangay) use ($regionPsgc, $provincePsgc, $cityPsgc) {
                return $barangay['region_psgc'] === $regionPsgc
                    && $barangay['province_psgc'] === $provincePsgc
                    && $barangay['city_psgc'] === $cityPsgc;
            })
            ->mapWithKeys(fn (array $barangay, string $id) => [$id => $barangay['name']])
            ->toArray();
    }

    protected function loadCoaTemplateData(): void
    {
        // OPTIMIZED: Cache COA data for 1 hour to avoid repeated DB queries
        // Updated cache key to v4 to force refresh after removing unique constraint on account_classes.code
        $cachedData = Cache::remember('coa_template_data_v4', 3600, function () {
            // Normalize all IDs to strings for consistent client-side matching
            $classCodes = AccountClass::where('is_active', true)
                ->orderBy('code')
                ->get(['id', 'code'])
                ->mapWithKeys(fn ($class) => [(string) $class->id => str_pad((string) $class->code, 1, '0', STR_PAD_LEFT)])
                ->toArray();

            // Subclass orders: Sort by sort_order (ascending), then assign sequential numbers starting from 1
            $subclassOrders = AccountSubclass::where('is_active', true)
                ->orderBy('account_class_id')
                ->orderBy('sort_order') // Sort by sort_order first
                ->orderBy('name') // Then by name as secondary sort
                ->get(['id', 'account_class_id', 'sort_order', 'name'])
                ->groupBy('account_class_id')
                ->map(function ($group) {
                    // Sort each group by sort_order (ascending) first, then by name if sort_orders are equal
                    return $group->sortBy(function ($item) {
                        // Primary sort: sort_order (ascending), secondary: name (ascending)
                        return [$item->sort_order ?? 1, $item->name ?? ''];
                    })->values();
                })
                ->flatMap(fn ($group) => $group->mapWithKeys(fn ($subclass, $index) => [(string) $subclass->id => $index + 1]))
                ->toArray();

            // Type orders: Sort by sort_order (ascending), then assign sequential numbers starting from 1
            $typeOrders = AccountType::where('is_active', true)
                ->orderBy('account_subclass_id')
                ->orderBy('sort_order') // Sort by sort_order first
                ->orderBy('name') // Then by name as secondary sort
                ->get(['id', 'account_subclass_id', 'sort_order', 'name'])
                ->groupBy('account_subclass_id')
                ->map(function ($group) {
                    // Sort each group by sort_order (ascending) first, then by name if sort_orders are equal
                    return $group->sortBy(function ($item) {
                        // Primary sort: sort_order (ascending), secondary: name (ascending)
                        return [$item->sort_order ?? 1, $item->name ?? ''];
                    })->values();
                })
                ->flatMap(fn ($group) => $group->mapWithKeys(fn ($type, $index) => [(string) $type->id => $index + 1]))
                ->toArray();

            // Subtype orders: Sort by sort_order (ascending), then assign sequential numbers starting from 0
            $subtypeOrders = AccountSubtype::where('is_active', true)
                ->orderBy('account_type_id')
                ->orderBy('sort_order') // Sort by sort_order first
                ->orderBy('name') // Then by name as secondary sort
                ->get(['id', 'account_type_id', 'sort_order', 'name'])
                ->groupBy('account_type_id')
                ->map(function ($group) {
                    // Sort each group by sort_order (ascending) first, then by name if sort_orders are equal
                    return $group->sortBy(function ($item) {
                        // Primary sort: sort_order (ascending), secondary: name (ascending)
                        return [$item->sort_order ?? 0, $item->name ?? ''];
                    })->values();
                })
                ->flatMap(fn ($group) => $group->mapWithKeys(fn ($subtype, $index) => [(string) $subtype->id => $index]))
                ->toArray();

            // Use a single efficient query with left join instead of deep eager loading
            // Note: account_classes.code is no longer unique, but we join by ID so this is fine
            $coaItems = DB::table('coa_template_items as cti')
                ->select([
                    'cti.id',
                    'cti.account_name',
                    'cti.normal_balance',
                    'cti.is_default',
                    'cti.is_active',
                    'cti.account_subtype_id',
                    'ast.name as account_subtype_name',
                    'ast.account_type_id',
                    'aty.name as account_type_name',
                    'aty.account_subclass_id',
                    'asc.name as account_subclass_name',
                    'asc.account_class_id',
                    'acl.id as account_class_id_direct',
                    'acl.name as account_class_name',
                    'acl.code as account_class_code',
                ])
                ->leftJoin('account_subtypes as ast', 'cti.account_subtype_id', '=', 'ast.id')
                ->leftJoin('account_types as aty', 'ast.account_type_id', '=', 'aty.id')
                ->leftJoin('account_subclasses as asc', 'aty.account_subclass_id', '=', 'asc.id')
                ->leftJoin('account_classes as acl', 'asc.account_class_id', '=', 'acl.id')
                ->where('cti.is_active', true)
                ->orderBy('cti.id', 'asc')
                ->get()
                ->map(function ($item) {
                    // Debug: Log first few items to check data
                    static $logged = 0;
                    if ($logged < 3 && $item->id) {
                        Log::debug('COA Item sample data #' . ($logged + 1), [
                            'id' => $item->id,
                            'account_name' => $item->account_name,
                            'account_subtype_id' => $item->account_subtype_id,
                            'account_type_id' => $item->account_type_id,
                            'account_subclass_id' => $item->account_subclass_id,
                            'account_class_id' => $item->account_class_id,
                            'account_class_name' => $item->account_class_name,
                            'account_subclass_name' => $item->account_subclass_name,
                            'account_type_name' => $item->account_type_name,
                            'account_subtype_name' => $item->account_subtype_name,
                            'account_class_code' => $item->account_class_code,
                        ]);
                        $logged++;
                    }

                    // Check if values are null or empty string - be more explicit
                    $accountClassName = isset($item->account_class_name) && $item->account_class_name !== '' && $item->account_class_name !== null
                        ? (string) $item->account_class_name
                        : 'N/A';
                    $accountSubclassName = isset($item->account_subclass_name) && $item->account_subclass_name !== '' && $item->account_subclass_name !== null
                        ? (string) $item->account_subclass_name
                        : 'N/A';
                    $accountTypeName = isset($item->account_type_name) && $item->account_type_name !== '' && $item->account_type_name !== null
                        ? (string) $item->account_type_name
                        : 'N/A';
                    $accountSubtypeName = isset($item->account_subtype_name) && $item->account_subtype_name !== '' && $item->account_subtype_name !== null
                        ? (string) $item->account_subtype_name
                        : 'N/A';

                    // Use account_class_id_direct if available, otherwise fall back to account_class_id from subclass
                    $accountClassId = $item->account_class_id_direct ?? $item->account_class_id;

                    return [
                        'id' => (string) $item->id, // Normalize to string for consistent client-side matching
                        'account_name' => $item->account_name,
                        'account_class_name' => $accountClassName,
                        'account_subclass_name' => $accountSubclassName,
                        'account_type_name' => $accountTypeName,
                        'account_subtype' => $accountSubtypeName,
                        'account_subtype_id' => $item->account_subtype_id ? (string) $item->account_subtype_id : null,
                        'account_type_id' => $item->account_type_id ? (string) $item->account_type_id : null,
                        'account_subclass_id' => $item->account_subclass_id ? (string) $item->account_subclass_id : null,
                        'account_class_id' => $accountClassId ? (string) $accountClassId : null,
                        'account_class_code' => $item->account_class_code ? (string) $item->account_class_code : null,
                        'normal_balance' => $item->normal_balance ?? 'debit',
                        'normal_balance_label' => ucfirst($item->normal_balance ?? 'debit'),
                        'is_default' => (bool) $item->is_default,
                        'is_active' => (bool) $item->is_active,
                    ];
                })
                ->toArray();

            // Use raw queries to fetch and group relationships in bulk (much faster)
            // Normalize all IDs to strings for consistent client-side matching
            $businessTypeRelations = [];
            foreach (DB::table('coa_item_business_types')->select('business_type_id', 'account_item_id')->get() as $relation) {
                $businessTypeId = (string) $relation->business_type_id;
                $accountItemId = (string) $relation->account_item_id;
                if (!isset($businessTypeRelations[$businessTypeId])) {
                    $businessTypeRelations[$businessTypeId] = [];
                }
                $businessTypeRelations[$businessTypeId][] = $accountItemId;
            }

            $industryTypeRelations = [];
            foreach (DB::table('coa_item_industry_types')->select('industry_type_id', 'account_item_id')->get() as $relation) {
                $industryTypeId = (string) $relation->industry_type_id;
                $accountItemId = (string) $relation->account_item_id;
                if (!isset($industryTypeRelations[$industryTypeId])) {
                    $industryTypeRelations[$industryTypeId] = [];
                }
                $industryTypeRelations[$industryTypeId][] = $accountItemId;
            }

            $taxTypeRelations = [];
            foreach (DB::table('coa_item_tax_types')->select('tax_type_id', 'account_item_id')->get() as $relation) {
                $taxTypeId = (string) $relation->tax_type_id;
                $accountItemId = (string) $relation->account_item_id;
                if (!isset($taxTypeRelations[$taxTypeId])) {
                    $taxTypeRelations[$taxTypeId] = [];
                }
                $taxTypeRelations[$taxTypeId][] = $accountItemId;
            }

            // Load hierarchy data for COA item creation
            $accountClasses = AccountClass::where('is_active', true)
                ->orderBy('code')
                ->get()
                ->map(function ($class) {
                    return [
                        'id' => (string) $class->id,
                        'code' => $class->code,
                        'name' => $class->name,
                    ];
                })
                ->toArray();

            $accountSubclasses = AccountSubclass::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($subclass) {
                    return [
                        'id' => (string) $subclass->id,
                        'account_class_id' => (string) $subclass->account_class_id,
                        'name' => $subclass->name,
                        'sort_order' => $subclass->sort_order ?? 1,
                    ];
                })
                ->toArray();

            $accountTypes = AccountType::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($type) {
                    return [
                        'id' => (string) $type->id,
                        'account_subclass_id' => (string) $type->account_subclass_id,
                        'name' => $type->name,
                        'sort_order' => $type->sort_order ?? 1,
                    ];
                })
                ->toArray();

            $accountSubtypes = AccountSubtype::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($subtype) {
                    return [
                        'id' => (string) $subtype->id,
                        'account_type_id' => (string) $subtype->account_type_id,
                        'name' => $subtype->name,
                        'sort_order' => $subtype->sort_order ?? 0,
                    ];
                })
                ->toArray();

            return [
                'items' => $coaItems,
                'businessTypes' => $businessTypeRelations,
                'industryTypes' => $industryTypeRelations,
                'taxTypes' => $taxTypeRelations,
                'structure' => [
                    'classCodes' => $classCodes,
                    'subclassOrders' => $subclassOrders,
                    'typeOrders' => $typeOrders,
                    'subtypeOrders' => $subtypeOrders,
                ],
                'hierarchy' => [
                    'accountClasses' => $accountClasses,
                    'accountSubclasses' => $accountSubclasses,
                    'accountTypes' => $accountTypes,
                    'accountSubtypes' => $accountSubtypes,
                ],
            ];
        });

        $this->allCoaItems = $cachedData['items'];
        $this->coaItemsByBusinessType = $cachedData['businessTypes'];
        $this->coaItemsByIndustryType = $cachedData['industryTypes'];
        $this->coaItemsByTaxType = $cachedData['taxTypes'];
        $this->coaStructure = $cachedData['structure'];

        // Handle hierarchy data with fallback for old cached data
        $hierarchy = $cachedData['hierarchy'] ?? [];
        $this->accountClasses = $hierarchy['accountClasses'] ?? [];
        $this->accountSubclasses = $hierarchy['accountSubclasses'] ?? [];
        $this->accountTypes = $hierarchy['accountTypes'] ?? [];
        $this->accountSubtypes = $hierarchy['accountSubtypes'] ?? [];

        // If hierarchy data is missing, load it directly (shouldn't happen with v3 cache, but safety fallback)
        if (empty($this->accountClasses)) {
            $this->loadHierarchyDataFallback();
        }
    }

    protected function loadHierarchyDataFallback(): void
    {
        $this->accountClasses = AccountClass::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($class) {
                return [
                    'id' => (string) $class->id,
                    'code' => $class->code,
                    'name' => $class->name,
                ];
            })
            ->toArray();

        $this->accountSubclasses = AccountSubclass::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($subclass) {
                return [
                    'id' => (string) $subclass->id,
                    'account_class_id' => (string) $subclass->account_class_id,
                    'name' => $subclass->name,
                    'sort_order' => $subclass->sort_order ?? 1,
                ];
            })
            ->toArray();

        $this->accountTypes = AccountType::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => (string) $type->id,
                    'account_subclass_id' => (string) $type->account_subclass_id,
                    'name' => $type->name,
                    'sort_order' => $type->sort_order ?? 1,
                ];
            })
            ->toArray();

        $this->accountSubtypes = AccountSubtype::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($subtype) {
                return [
                    'id' => (string) $subtype->id,
                    'account_type_id' => (string) $subtype->account_type_id,
                    'name' => $subtype->name,
                    'sort_order' => $subtype->sort_order ?? 0,
                ];
            })
            ->toArray();
    }

    protected function sanitizePostalCode(?string $postalCode): ?string
    {
        if (! $postalCode) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $postalCode);

        return preg_match('/^\d{4}$/', $digits) ? $digits : null;
    }

    protected function collectSelectedTaxTypeIds(): array
    {
        if (! $this->isBirSelected()) {
            return [];
        }

        $ids = [];

        foreach ($this->taxCategories as $categoryId => $meta) {
            $selection = $this->taxSelections[$categoryId] ?? ($meta['is_additional'] ? [] : null);

            if ($meta['is_additional']) {
                $values = array_filter((array) $selection, fn ($value) => filled($value));
                $ids = array_merge($ids, array_map('strval', $values));
            } elseif (filled($selection)) {
                $ids[] = (string) $selection;
            }
        }

        $ids = array_filter($ids, fn ($value) => filled($value));

        return array_values(array_unique($ids));
    }

    protected function isBirSelected(): bool
    {
        return filled($this->birAgencyId) && in_array($this->birAgencyId, $this->government_agency_ids, true);
    }

    protected function updateCoaPreview(): void
    {
        // COA preview is now computed client-side for responsiveness.
        $this->coaPreview = [];
    }

    protected function getStepRules(int $step): array
    {
        $regionIds = array_map('strval', array_keys($this->regions));
        $provinceIds = array_map('strval', array_keys($this->provinceLookup));
        $cityIds = array_map('strval', array_keys($this->cityLookup));
        $barangayIds = array_map('strval', array_keys($this->barangayLookup));
        $industryTypeIds = array_map('strval', array_keys($this->industryTypes));
        $fiscalPeriodIds = array_map('strval', array_keys($this->fiscalYearPeriods));
        $businessTypeIds = array_map('strval', array_keys($this->businessTypes));
        $agencyIds = array_map('strval', array_keys($this->governmentAgencies));

        return match ($step) {
            1 => [
                'business_name' => ['required', 'string', 'max:255'],
                'tin_number' => ['required', 'regex:/^\d{3}-\d{3}-\d{3}-\d{3}$/'],
                'business_email' => ['required', 'email', 'max:255'],
                'region_id' => ['required', Rule::in($regionIds)],
                'province_id' => ['required', Rule::in($provinceIds)],
                'city_id' => ['required', Rule::in($cityIds)],
                'barangay_id' => ['required', Rule::in($barangayIds)],
                'postal_code' => ['nullable', 'regex:/^\d{4}$/'],
            ],
            2 => [
                'industry_type_ids' => ['required', 'array', 'min:1'],
                'industry_type_ids.*' => [Rule::in($industryTypeIds)],
            ],
            3 => [
                'fiscal_year_period_id' => ['required', Rule::in($fiscalPeriodIds)],
            ],
            4 => [
                'business_type_id' => ['required', Rule::in($businessTypeIds)],
            ],
            5 => [
                'government_agency_ids' => ['required', 'array', 'min:1'],
                'government_agency_ids.*' => [Rule::in($agencyIds)],
            ],
            default => [],
        };
    }

    protected function getAllRules(): array
    {
        return array_merge(
            $this->getStepRules(1),
            $this->getStepRules(2),
            $this->getStepRules(3),
            $this->getStepRules(4),
            $this->getStepRules(5),
        );
    }
}
