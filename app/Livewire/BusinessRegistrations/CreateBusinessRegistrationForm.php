<?php

namespace App\Livewire\BusinessRegistrations;

use App\Models\BusinessGovernmentRegistration;
use App\Models\BusinessRegistration;
use App\Models\BusinessType;
use App\Models\COAItemBusinessType;
use App\Models\COAItemIndustryType;
use App\Models\COAItemTaxType;
use App\Models\COATemplateItem;
use App\Models\FiscalYearPeriod;
use App\Models\GovernmentAgency;
use App\Models\IndustryType;
use App\Models\TaxCategory;
use App\Models\TaxType;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    public array $regionLookup = [];
    public array $provinceLookup = [];
    public array $cityLookup = [];
    public array $barangayLookup = [];

    // COA Template Data for client-side filtering
    public array $allCoaItems = [];
    public array $coaItemsByBusinessType = [];
    public array $coaItemsByIndustryType = [];
    public array $coaItemsByTaxType = [];

    public ?string $birAgencyId = null;

    public function mount(): void
    {
        $this->loadStaticOptions();
        $this->initializeTaxSelections();
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
        $this->validate($this->getAllRules());

        DB::transaction(function () {
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
                'user_id' => Auth::id(),
            ];

            /** @var BusinessRegistration $businessRegistration */
            $businessRegistration = BusinessRegistration::create($data);

            if (! empty($this->industry_type_ids)) {
                $businessRegistration->industryTypes()->sync($this->industry_type_ids);
            }

            $taxTypeIds = $this->collectSelectedTaxTypeIds();
            if (! empty($taxTypeIds)) {
                $businessRegistration->taxTypes()->sync($taxTypeIds);
            }

            foreach ($this->government_agency_ids as $agencyId) {
                BusinessGovernmentRegistration::create([
                    'business_registration_id' => $businessRegistration->id,
                    'government_agency_id' => $agencyId,
                    'is_active' => true,
                ]);
            }
        });

        Notification::make()
            ->success()
            ->title('Business registration created')
            ->body('The business registration record is now available in the listings.')
            ->send();

        $this->redirectRoute('filament.app.resources.business-registrations.index');
    }

    public function getCoaPreview(): array
    {
        $this->updateCoaPreview();
        return $this->coaPreview;
    }

    public function render()
    {
        return view('livewire.business-registrations.create-business-registration-form');
    }

    protected function loadStaticOptions(): void
    {
        // Load all COA template items with relationships for client-side filtering
        $this->loadCoaTemplateData();

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
        // Load all active COA template items with relationships
        $coaItems = COATemplateItem::query()
            ->where('is_active', true)
            ->with('accountSubtype.accountType.accountSubclass.accountClass')
            ->orderBy('account_code')
            ->get()
            ->map(function (COATemplateItem $item) {
                return [
                    'id' => $item->id,
                    'account_code' => $item->account_code,
                    'account_name' => $item->account_name,
                    'account_subtype' => $item->accountSubtype?->name ?? 'N/A',
                    'normal_balance' => ucfirst($item->normal_balance ?? 'N/A'),
                    'is_default' => $item->is_default,
                ];
            })
            ->toArray();

        $this->allCoaItems = $coaItems;

        // Build lookup tables for each item ID by type
        $businessTypeRelations = COAItemBusinessType::query()->get();
        foreach ($businessTypeRelations as $relation) {
            $this->coaItemsByBusinessType[$relation->business_type_id][] = $relation->account_item_id;
        }

        $industryTypeRelations = COAItemIndustryType::query()->get();
        foreach ($industryTypeRelations as $relation) {
            $this->coaItemsByIndustryType[$relation->industry_type_id][] = $relation->account_item_id;
        }

        $taxTypeRelations = COAItemTaxType::query()->get();
        foreach ($taxTypeRelations as $relation) {
            $this->coaItemsByTaxType[$relation->tax_type_id][] = $relation->account_item_id;
        }
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
        $coaItemIds = COATemplateItem::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if ($this->business_type_id) {
            $businessTypeCoaIds = COAItemBusinessType::query()
                ->where('business_type_id', $this->business_type_id)
                ->pluck('account_item_id')
                ->toArray();
            $coaItemIds = array_merge($coaItemIds, $businessTypeCoaIds);
        }

        if (! empty($this->industry_type_ids)) {
            $industryTypeCoaIds = COAItemIndustryType::query()
                ->whereIn('industry_type_id', $this->industry_type_ids)
                ->pluck('account_item_id')
                ->toArray();
            $coaItemIds = array_merge($coaItemIds, $industryTypeCoaIds);
        }

        $taxTypeIds = $this->collectSelectedTaxTypeIds();
        if (! empty($taxTypeIds)) {
            $taxTypeCoaIds = COAItemTaxType::query()
                ->whereIn('tax_type_id', $taxTypeIds)
                ->pluck('account_item_id')
                ->toArray();
            $coaItemIds = array_merge($coaItemIds, $taxTypeCoaIds);
        }

        $coaItemIds = array_values(array_unique($coaItemIds));

        if (empty($coaItemIds)) {
            $this->coaPreview = [];

            return;
        }

        $this->coaPreview = COATemplateItem::query()
            ->whereIn('id', $coaItemIds)
            ->where('is_active', true)
            ->with('accountSubtype.accountType.accountSubclass.accountClass')
            ->orderBy('account_code')
            ->get()
            ->map(function (COATemplateItem $item) {
                return [
                    'account_code' => $item->account_code,
                    'account_name' => $item->account_name,
                    'account_subtype' => $item->accountSubtype?->name ?? 'N/A',
                    'normal_balance' => ucfirst($item->normal_balance ?? 'N/A'),
                ];
            })
            ->toArray();
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
