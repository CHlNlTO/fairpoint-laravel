<div class="space-y-6" x-data="{
    currentStep: 1,

    // Form data - all stored client-side
    business_name: '',
    tin_number: '',
    business_email: '',
    regionId: '',
    provinceId: '',
    cityId: '',
    barangayId: '',
    street_address: '',
    building_name: '',
    unit_number: '',
    postal_code: '',
    industry_type_ids: [],
    fiscal_year_period_id: '',
    business_type_id: '',
    government_agency_ids: [],
    taxSelections: @js($taxSelections),

    // COA Preview - preloaded for instant filtering
    coaPreview: [],
    loadingCoaPreview: false,
    allCoaItems: @js($allCoaItems),
    coaItemsByBusinessType: @js($coaItemsByBusinessType),
    coaItemsByIndustryType: @js($coaItemsByIndustryType),
    coaItemsByTaxType: @js($coaItemsByTaxType),

    // Lookup data
    allRegions: @js($regions),
    allProvincesLookup: @js($provinceLookup),
    allCitiesLookup: @js($cityLookup),
    allBarangaysLookup: @js($barangayLookup),
    allRegionsLookup: @js($regionLookup),

    // Filtered options (cached for performance)
    filteredProvinces: {},
    filteredCities: {},
    filteredBarangays: {},

    init() {
        // Pre-filter and cache all provinces, cities, and barangays by their parent IDs
        // This makes filtering instant when a selection is made
        this.cacheFilteredOptions();

        this.$watch('regionId', (newValue) => {
            this.provinceId = '';
            this.cityId = '';
            this.barangayId = '';
            this.updateFilteredProvinces();
            this.filteredCities = {};
            this.filteredBarangays = {};
        });

        this.$watch('provinceId', (newValue) => {
            this.cityId = '';
            this.barangayId = '';
            this.updateFilteredCities();
            this.filteredBarangays = {};
        });

        this.$watch('cityId', (newValue) => {
            this.barangayId = '';
            this.updateFilteredBarangays();
        });

        // Watch for changes that affect COA preview
        this.$watch('business_type_id', () => {
            if (this.currentStep === 7) {
                this.filterCoaItems();
            }
        });

        this.$watch('industry_type_ids', () => {
            if (this.currentStep === 7) {
                this.filterCoaItems();
            }
        });

        this.$watch('government_agency_ids', () => {
            if (this.currentStep === 7) {
                this.filterCoaItems();
            }
        });

        this.$watch('taxSelections', () => {
            if (this.currentStep === 7 && this.isBirSelected()) {
                this.filterCoaItems();
            }
        }, { deep: true });

        this.$watch('currentStep', (newStep) => {
            if (newStep === 7) {
                this.filterCoaItems();
            }
        });
    },

    cacheFilteredOptions() {
        // Pre-build lookup maps for instant filtering
        this.provincesByRegion = {};
        this.citiesByProvince = {};
        this.barangaysByCity = {};

        // Cache provinces by region
        Object.entries(this.allProvincesLookup).forEach(([id, province]) => {
            const regionPsgc = province.region_psgc;
            if (!this.provincesByRegion[regionPsgc]) {
                this.provincesByRegion[regionPsgc] = {};
            }
            this.provincesByRegion[regionPsgc][id] = province.name;
        });

        // Cache cities by province
        Object.entries(this.allCitiesLookup).forEach(([id, city]) => {
            const provincePsgc = city.province_psgc;
            if (!this.citiesByProvince[provincePsgc]) {
                this.citiesByProvince[provincePsgc] = {};
            }
            this.citiesByProvince[provincePsgc][id] = city.name;
        });

        // Cache barangays by city
        Object.entries(this.allBarangaysLookup).forEach(([id, barangay]) => {
            const cityPsgc = barangay.city_psgc;
            if (!this.barangaysByCity[cityPsgc]) {
                this.barangaysByCity[cityPsgc] = {};
            }
            this.barangaysByCity[cityPsgc][id] = barangay.name;
        });
    },

    updateFilteredProvinces() {
        if (!this.regionId) {
            this.filteredProvinces = {};
            return;
        }
        const region = this.allRegionsLookup[this.regionId];
        if (!region) {
            this.filteredProvinces = {};
            return;
        }
        this.filteredProvinces = this.provincesByRegion[region.psgc] || {};
    },

    updateFilteredCities() {
        if (!this.provinceId) {
            this.filteredCities = {};
            return;
        }
        const province = this.allProvincesLookup[this.provinceId];
        if (!province) {
            this.filteredCities = {};
            return;
        }
        this.filteredCities = this.citiesByProvince[province.psgc] || {};
    },

    updateFilteredBarangays() {
        if (!this.cityId) {
            this.filteredBarangays = {};
            return;
        }
        const city = this.allCitiesLookup[this.cityId];
        if (!city) {
            this.filteredBarangays = {};
            return;
        }
        this.filteredBarangays = this.barangaysByCity[city.psgc] || {};
    },

    nextStep() {
        // Skip step 6 (Tax Types) if BIR is not selected
        if (this.currentStep === 5 && !this.isBirSelected()) {
            this.currentStep = 7; // Skip to COA preview
            return;
        }

        if (this.currentStep < 7) {
            this.currentStep++;
        }
    },

    previousStep() {
        // Skip step 6 (Tax Types) if BIR is not selected when going back
        if (this.currentStep === 7 && !this.isBirSelected()) {
            this.currentStep = 5; // Skip back to Gov Agencies
            return;
        }

        if (this.currentStep > 1) {
            this.currentStep--;
        }
    },

    isBirSelected() {
        const birAgencyId = '{{ $birAgencyId }}';
        return birAgencyId && this.government_agency_ids.includes(birAgencyId);
    },

    filterCoaItems() {
        // Client-side filtering - instant results!
        const itemIds = new Set();

        // Get all default items
        this.allCoaItems.forEach(item => {
            if (item.is_default) {
                itemIds.add(item.id);
            }
        });

        // Add items by business type
        if (this.business_type_id) {
            const btItems = this.coaItemsByBusinessType[this.business_type_id] || [];
            btItems.forEach(id => itemIds.add(id));
        }

        // Add items by industry types
        this.industry_type_ids.forEach(industryId => {
            const itItems = this.coaItemsByIndustryType[industryId] || [];
            itItems.forEach(id => itemIds.add(id));
        });

        // Add items by tax types (collect all selected tax types)
        const selectedTaxTypes = [];
        Object.values(this.taxSelections).forEach(selection => {
            if (Array.isArray(selection)) {
                selectedTaxTypes.push(...selection);
            } else if (selection) {
                selectedTaxTypes.push(selection);
            }
        });

        selectedTaxTypes.forEach(taxId => {
            const ttItems = this.coaItemsByTaxType[taxId] || [];
            ttItems.forEach(id => itemIds.add(id));
        });

        // Filter items by IDs
        this.coaPreview = this.allCoaItems.filter(item => itemIds.has(item.id));
    },

    async submitForm() {
        // Batch sync all form data to Livewire in one go
        const formData = {
            business_name: this.business_name,
            tin_number: this.tin_number,
            business_email: this.business_email,
            region_id: this.regionId,
            province_id: this.provinceId,
            city_id: this.cityId,
            barangay_id: this.barangayId,
            street_address: this.street_address,
            building_name: this.building_name,
            unit_number: this.unit_number,
            postal_code: this.postal_code,
            industry_type_ids: this.industry_type_ids,
            fiscal_year_period_id: this.fiscal_year_period_id,
            business_type_id: this.business_type_id,
            government_agency_ids: this.government_agency_ids,
            taxSelections: this.taxSelections
        };

        // Sync all at once
        for (const [key, value] of Object.entries(formData)) {
            $wire.set(key, value, false);
        }

        // Then submit
        $wire.submit();
    }
}">
    @php
        $steps = [
            1 => 'Basic Information',
            2 => 'Industry Types',
            3 => 'Fiscal Year',
            4 => 'Business Type',
            5 => 'Government Agencies',
            6 => 'Tax Types',
            7 => 'Chart of Accounts',
        ];
    @endphp

    <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-4 md:p-6">
        <ol class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            @foreach ($steps as $stepNumber => $label)
                <li class="flex items-start gap-3 md:flex-1">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full border text-sm font-semibold"
                          :class="currentStep >= {{ $stepNumber }} ? 'bg-primary-600 border-primary-600 text-white' : 'border-gray-300 text-gray-500 dark:border-gray-600 dark:text-gray-400'">
                        {{ $stepNumber }}
                    </span>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $label }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            @switch($stepNumber)
                                @case(1)Company and address details.@break
                                @case(2)Choose applicable industries.@break
                                @case(3)Set reporting period.@break
                                @case(4)Pick business configuration.@break
                                @case(5)Select government agencies.@break
                                @case(6)Configure tax types if needed.@break
                                @case(7)Review account selections.@break
                            @endswitch
                        </span>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>

    <form @submit.prevent="submitForm()" class="space-y-6">
        <div x-show="currentStep === 1">
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Basic Information</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Provide the core details for the business registration.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Business Name -->
                    <div class="space-y-2">
                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="business_name">
                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                Business Name
                                <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                            </span>
                        </label>
                        <div class="fi-input-wrp fi-fo-text-input">
                            <div class="fi-input-wrp-content-ctn">
                                <input id="business_name"
                                       type="text"
                                       x-model="business_name"
                                       class="fi-input"
                                       placeholder="Enter business name">
                            </div>
                        </div>
                    </div>

                    <!-- TIN -->
                    <div class="space-y-2">
                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="tin_number">
                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                TIN
                                <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                            </span>
                        </label>
                        <div class="fi-input-wrp fi-fo-text-input">
                            <div class="fi-input-wrp-content-ctn">
                                <input id="tin_number"
                                       type="text"
                                       x-model="tin_number"
                                       class="fi-input"
                                       placeholder="XXX-XXX-XXX-XXX">
                            </div>
                        </div>
                    </div>

                    <!-- Business Email -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="business_email">
                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                Business Email
                                <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                            </span>
                        </label>
                        <div class="fi-input-wrp fi-fo-text-input">
                            <div class="fi-input-wrp-content-ctn">
                                <input id="business_email"
                                       type="email"
                                       x-model="business_email"
                                       class="fi-input"
                                       placeholder="name@example.com">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Business Address</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Region -->
                        <div class="space-y-2">
                            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="region_id">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Region
                                    <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                </span>
                            </label>
                            <div class="fi-input-wrp fi-fo-select">
                                <div class="fi-input-wrp-content-ctn">
                                    <select id="region_id"
                                            x-model="regionId"
                                            class="fi-select-input fi-input">
                                        <option value="">Select region</option>
                                        <template x-for="(name, id) in allRegions" :key="id">
                                            <option :value="id" x-text="name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Province -->
                        <div class="space-y-2">
                            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="province_id">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Province
                                    <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                </span>
                            </label>
                            <div class="fi-input-wrp fi-fo-select" :class="{ 'fi-disabled': !regionId || Object.keys(filteredProvinces).length === 0 }">
                                <div class="fi-input-wrp-content-ctn">
                                    <select id="province_id"
                                            x-model="provinceId"
                                            :disabled="!regionId || Object.keys(filteredProvinces).length === 0"
                                            class="fi-select-input fi-input">
                                        <option value="">Select province</option>
                                        <template x-for="(name, id) in filteredProvinces" :key="id">
                                            <option :value="id" x-text="name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- City / Municipality -->
                        <div class="space-y-2">
                            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="city_id">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    City / Municipality
                                    <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                </span>
                            </label>
                            <div class="fi-input-wrp fi-fo-select" :class="{ 'fi-disabled': !provinceId || Object.keys(filteredCities).length === 0 }">
                                <div class="fi-input-wrp-content-ctn">
                                    <select id="city_id"
                                            x-model="cityId"
                                            :disabled="!provinceId || Object.keys(filteredCities).length === 0"
                                            class="fi-select-input fi-input">
                                        <option value="">Select city or municipality</option>
                                        <template x-for="(name, id) in filteredCities" :key="id">
                                            <option :value="id" x-text="name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Barangay -->
                        <div class="space-y-2">
                            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="barangay_id">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Barangay
                                    <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                </span>
                            </label>
                            <div class="fi-input-wrp fi-fo-select" :class="{ 'fi-disabled': !cityId || Object.keys(filteredBarangays).length === 0 }">
                                <div class="fi-input-wrp-content-ctn">
                                    <select id="barangay_id"
                                            x-model="barangayId"
                                            :disabled="!cityId || Object.keys(filteredBarangays).length === 0"
                                            class="fi-select-input fi-input">
                                        <option value="">Select barangay</option>
                                        <template x-for="(name, id) in filteredBarangays" :key="id">
                                            <option :value="id" x-text="name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Street Address -->
                        <div class="space-y-2">
                            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="street_address">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Street Address
                                </span>
                            </label>
                            <div class="fi-input-wrp fi-fo-text-input">
                                <div class="fi-input-wrp-content-ctn">
                                    <input id="street_address"
                                           type="text"
                                           x-model="street_address"
                                           class="fi-input"
                                           placeholder="Street, subdivision, etc.">
                                </div>
                            </div>
                        </div>

                        <!-- Building Name -->
                        <div class="space-y-2">
                            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="building_name">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Building Name
                                </span>
                            </label>
                            <div class="fi-input-wrp fi-fo-text-input">
                                <div class="fi-input-wrp-content-ctn">
                                    <input id="building_name"
                                           type="text"
                                           x-model="building_name"
                                           class="fi-input"
                                           placeholder="Optional building name">
                                </div>
                            </div>
                        </div>

                        <!-- Unit / Floor No. -->
                        <div class="space-y-2">
                            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="unit_number">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Unit / Floor No.
                                </span>
                            </label>
                            <div class="fi-input-wrp fi-fo-text-input">
                                <div class="fi-input-wrp-content-ctn">
                                    <input id="unit_number"
                                           type="text"
                                           x-model="unit_number"
                                           class="fi-input"
                                           placeholder="Optional unit or floor">
                                </div>
                            </div>
                        </div>

                        <!-- Postal Code -->
                        <div class="space-y-2">
                            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="postal_code">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    Postal Code
                                </span>
                            </label>
                            <div class="fi-input-wrp fi-fo-text-input">
                                <div class="fi-input-wrp-content-ctn">
                                    <input id="postal_code"
                                           type="text"
                                           x-model="postal_code"
                                           maxlength="4"
                                           class="fi-input"
                                           placeholder="4-digit postal code">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="currentStep === 2">
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Industry Types</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Select all industry classifications that describe the business.</p>
                </div>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach ($industryTypes as $id => $label)
                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3 cursor-pointer hover:border-primary-400 dark:hover:border-primary-500 transition-colors">
                            <input type="checkbox"
                                   value="{{ $id }}"
                                   x-model="industry_type_ids"
                                   class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:checked:border-primary-600 dark:checked:bg-primary-600">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div x-show="currentStep === 3">
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Fiscal Year Period</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose the reporting period that aligns with the business operations.</p>
                </div>
                <div class="space-y-2">
                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3" for="fiscal_year_period_id">
                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                            Fiscal Year Period
                            <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                        </span>
                    </label>
                    <div class="fi-input-wrp fi-fo-select">
                        <div class="fi-input-wrp-content-ctn">
                            <select id="fiscal_year_period_id"
                                    x-model="fiscal_year_period_id"
                                    class="fi-select-input fi-input">
                                <option value="">Select period</option>
                                @foreach ($fiscalYearPeriods as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="currentStep === 4">
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Business Type</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Identify the business structure to tailor the Chart of Accounts.</p>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach ($businessTypes as $id => $label)
                        <label class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 cursor-pointer hover:border-primary-400 dark:hover:border-primary-500 transition-colors">
                            <input type="radio"
                                   name="business_type_id"
                                   value="{{ $id }}"
                                   x-model="business_type_id"
                                   class="mt-1 fi-radio-input h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:checked:border-primary-600 dark:checked:bg-primary-600">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Step 5: Government Agencies -->
        <div x-show="currentStep === 5">
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Government Agencies</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Select the agencies the business will register with.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach ($governmentAgencies as $id => $agency)
                        <label class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3 cursor-pointer hover:border-primary-400 dark:hover:border-primary-500 transition-colors">
                            <input type="checkbox"
                                   value="{{ $id }}"
                                   x-model="government_agency_ids"
                                   class="mt-1 fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:checked:border-primary-600 dark:checked:bg-primary-600">
                            <div>
                                <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $agency['name'] }}</span>
                                @if (!empty($agency['code']))
                                    <span class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $agency['code'] }}</span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Step 6: Tax Types (BIR Configuration) -->
        <div x-show="currentStep === 6">
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tax Types Configuration</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Configure the tax types applicable to your business for BIR registration.
                    </p>
                </div>

                <div class="rounded-xl border border-primary-200 dark:border-primary-500/60 bg-primary-50 dark:bg-primary-500/10 p-4 space-y-4">
                        <h3 class="text-sm font-semibold text-primary-700 dark:text-primary-300">BIR Tax Configuration</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach ($taxCategories as $categoryId => $meta)
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $meta['name'] }}</span>
                                        @if ($meta['is_additional'])
                                            <span class="text-xs text-primary-600 dark:text-primary-300 font-medium">Multiple selection</span>
                                        @endif
                                    </div>

                                    @if (empty($meta['tax_types']))
                                        <p class="text-sm text-gray-500 dark:text-gray-400">No active tax types available.</p>
                                    @elseif ($meta['is_additional'])
                                        <div class="space-y-2">
                                            @foreach ($meta['tax_types'] as $taxTypeId => $taxTypeName)
                                                <label class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                                    <input type="checkbox"
                                                           value="{{ $taxTypeId }}"
                                                           x-model="taxSelections.{{ $categoryId }}"
                                                           class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:checked:border-primary-600 dark:checked:bg-primary-600">
                                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $taxTypeName }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="fi-input-wrp fi-fo-select">
                                            <div class="fi-input-wrp-content-ctn">
                                                <select x-model="taxSelections.{{ $categoryId }}"
                                                        class="fi-select-input fi-input">
                                                    <option value="">Select tax type</option>
                                                    @foreach ($meta['tax_types'] as $taxTypeId => $taxTypeName)
                                                        <option value="{{ $taxTypeId }}">{{ $taxTypeName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                </div>
            </div>
        </div>


        <!-- Step 7: Chart of Accounts Preview -->
        <div x-show="currentStep === 7">
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div class="space-y-3">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Chart of Accounts Preview</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Based on your selections, these accounts will be connected to the business registration.</p>

                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" x-show="coaPreview.length > 0">
                                <thead class="bg-gray-50 dark:bg-gray-800/60">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Account Code</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Account Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Subtype</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Normal Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    <template x-for="item in coaPreview" :key="item.account_code">
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100" x-text="item.account_code"></td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.account_name"></td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.account_subtype"></td>
                                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.normal_balance"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        <p x-show="coaPreview.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            No accounts to display yet. Select business type, industries, or government agencies to see accounts.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="h-4 w-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>This list dynamically updates whenever you modify industries, business type, or tax selections.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="button"
                    @click="previousStep()"
                    :disabled="currentStep === 1"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Previous
            </button>

            <div class="flex items-center gap-3">
                <button type="button"
                        x-show="currentStep < 7"
                        @click="nextStep()"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-colors">
                    Next Step
                </button>
                <button type="submit"
                        x-show="currentStep === 7"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <span wire:loading wire:target="submit" class="h-4 w-4 animate-spin border-2 border-white border-t-transparent rounded-full"></span>
                    <span wire:loading.remove wire:target="submit">Create Registration</span>
                    <span wire:loading wire:target="submit">Saving...</span>
                </button>
            </div>
        </div>
    </form>
</div>
