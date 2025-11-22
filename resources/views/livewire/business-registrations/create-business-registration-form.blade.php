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

    // COA Preview - loaded upfront for instant filtering
    coaPreview: [],
    selectedCoaItems: [],
    allCoaItems: @js($allCoaItems),
    coaItemsByBusinessType: @js($coaItemsByBusinessType),
    coaItemsByIndustryType: @js($coaItemsByIndustryType),
    coaItemsByTaxType: @js($coaItemsByTaxType),
    coaStructure: @js($coaStructure),
    coaItemsById: {},

    // Hierarchy data for COA item creation
    accountClasses: @js($accountClasses),
    accountSubclasses: @js($accountSubclasses),
    accountTypes: @js($accountTypes),
    accountSubtypes: @js($accountSubtypes),

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

        // Initialize coaItemsById lookup map
        this.coaItemsById = (this.allCoaItems || []).reduce((acc, item) => {
            acc[String(item.id)] = item;
            return acc;
        }, {});

        // Debug: Log first item to check data structure
        if (this.allCoaItems && this.allCoaItems.length > 0) {
            console.log('First COA Item sample:', {
                id: this.allCoaItems[0].id,
                account_name: this.allCoaItems[0].account_name,
                account_class_name: this.allCoaItems[0].account_class_name,
                account_subclass_name: this.allCoaItems[0].account_subclass_name,
                account_type_name: this.allCoaItems[0].account_type_name,
                account_subtype: this.allCoaItems[0].account_subtype,
            });
        }

        // Initial filter
        this.filterCoaItems();

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
        // Always filter when selections change, not just on step 7
        this.$watch('business_type_id', () => {
            this.filterCoaItems();
        });

        this.$watch('industry_type_ids', () => {
            this.filterCoaItems();
        });

        this.$watch('government_agency_ids', () => {
            this.filterCoaItems();
        });

        this.$watch('taxSelections', () => {
            // Filter whenever tax selections change
            this.filterCoaItems();
        }, { deep: true });

        this.$watch('currentStep', (newStep) => {
            if (newStep === 7) {
                // COA data is already loaded - just filter it to reflect current state
                this.filterCoaItems();
            }
        });

        // Store reference to parent scope methods for nested Alpine contexts
        this.parentScope = {
            updateAccountCodeDebounced: (item) => this.updateAccountCodeDebounced(item),
            updateAccountCodeOnBlur: (item) => this.updateAccountCodeOnBlur(item),
            generateAccountCodeFromItem: (item) => this.generateAccountCodeFromItem(item),
            updateSelectedCoaItems: () => this.updateSelectedCoaItems(),
            onTypeChange: (item, typeId) => this.onTypeChange(item, typeId),
            removeCoaItemRow: (index) => this.removeCoaItemRow(index),
            addCoaItemRow: (index) => this.addCoaItemRow(index),
        };
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
        if (!Array.isArray(this.allCoaItems) || this.allCoaItems.length === 0) {
            this.coaPreview = [];
            this.selectedCoaItems = [];
            return;
        }

        // Ensure coaItemsById is populated
        if (Object.keys(this.coaItemsById).length === 0) {
            this.coaItemsById = this.allCoaItems.reduce((acc, item) => {
                // Normalize ID to string for consistent comparison
                acc[String(item.id)] = item;
                return acc;
            }, {});
        }

        const selectedIds = new Set();

        // Always include default items that are active
        this.allCoaItems.forEach(item => {
            if (item.is_default && item.is_active) {
                selectedIds.add(String(item.id));
            }
        });

        // Add items by business type
        if (this.business_type_id) {
            const businessTypeId = String(this.business_type_id);
            const businessTypeItems = this.coaItemsByBusinessType[businessTypeId] || [];
            businessTypeItems.forEach(id => {
                selectedIds.add(String(id));
            });
        }

        // Add items by industry types
        if (Array.isArray(this.industry_type_ids) && this.industry_type_ids.length > 0) {
            this.industry_type_ids.forEach(industryId => {
                const industryTypeId = String(industryId);
                const industryTypeItems = this.coaItemsByIndustryType[industryTypeId] || [];
                industryTypeItems.forEach(id => {
                    selectedIds.add(String(id));
                });
            });
        }

        // Add items by tax types (whenever tax types are selected)
        const selectedTaxTypeIds = this.collectSelectedTaxTypeIds();
        if (selectedTaxTypeIds.length > 0) {
            selectedTaxTypeIds.forEach(taxId => {
                const taxTypeId = String(taxId);
                const taxTypeItems = this.coaItemsByTaxType[taxTypeId] || [];
                taxTypeItems.forEach(id => {
                    selectedIds.add(String(id));
                });
            });
        }

        // Filter and enrich items
        const enrichedItems = Array.from(selectedIds)
            .map(id => this.coaItemsById[String(id)])
            .filter(item => item && item.is_active)
            .map(item => {
                const accountCode = this.generateAccountCode(item);
                return {
                    ...item,
                    account_code: accountCode,
                    normal_balance_label: this.formatBalance(item.normal_balance),
                    // Ensure all hierarchy names are properly set
                    account_class_name: item.account_class_name || 'N/A',
                    account_subclass_name: item.account_subclass_name || 'N/A',
                    account_type_name: item.account_type_name || 'N/A',
                    account_subtype: item.account_subtype || item.account_subtype_name || 'N/A',
                };
            })
            .sort((a, b) => a.account_code.localeCompare(b.account_code));

        // Debug logging (can be removed in production)
        if (this.currentStep === 7) {
            const selectedTaxTypeIds = this.collectSelectedTaxTypeIds();
            const taxTypeItemsCount = selectedTaxTypeIds.reduce((count, taxId) => {
                const taxTypeId = String(taxId);
                const items = this.coaItemsByTaxType[taxTypeId] || [];
                return count + items.length;
            }, 0);

            console.log('COA Filtering Debug:', {
                totalItems: this.allCoaItems.length,
                selectedIdsCount: selectedIds.size,
                businessTypeId: this.business_type_id,
                industryTypeIds: this.industry_type_ids,
                isBirSelected: this.isBirSelected(),
                taxTypeIds: selectedTaxTypeIds,
                taxTypeItemsCount: taxTypeItemsCount,
                coaItemsByTaxTypeKeys: Object.keys(this.coaItemsByTaxType || {}),
                enrichedItemsCount: enrichedItems.length,
            });
        }

        // Transform to editable format for preview
        // System items (isEditable: false) - only Account Name, Account Subtype, and Normal Balance are editable
        // User-added items (isEditable: true) - all fields are editable
        this.coaPreview = enrichedItems.map((item, index) => ({
            id: item.id || `temp-${index}`,
            isEditable: false, // Pre-filtered items from server are system items
            isUserAdded: false, // Mark as system item
            account_code: item.account_code,
            account_name: item.account_name,
            account_class_id: item.account_class_id || '',
            account_class_name: item.account_class_name ?? 'N/A',
            account_subclass_id: item.account_subclass_id || '',
            account_subclass_name: item.account_subclass_name ?? 'N/A',
            account_type_id: item.account_type_id || '',
            account_type_name: item.account_type_name ?? 'N/A',
            account_subtype_name: item.account_subtype ?? 'N/A',
            normal_balance: item.normal_balance || 'debit',
            normal_balance_label: item.normal_balance_label || 'Debit',
        }));

        this.selectedCoaItems = enrichedItems.map(item => ({
            coa_item_id: item.id,
            account_code: item.account_code,
            account_class: item.account_class_name ?? 'N/A',
            account_subclass: item.account_subclass_name ?? 'N/A',
            account_type: item.account_type_name ?? 'N/A',
            account_subtype: item.account_subtype ?? 'N/A',
            normal_balance: item.normal_balance,
            is_active: item.is_active,
        }));
    },

    addCoaItemRow(insertAfterIndex) {
        const newItem = {
            id: `new-${Date.now()}-${Math.random()}`,
            isEditable: true,
            isUserAdded: true, // Mark as user-added item
            account_code: '',
            account_name: '',
            account_class_id: '',
            account_class_name: '',
            account_subclass_id: '',
            account_subclass_name: '',
            account_type_id: '',
            account_type_name: '',
            account_subtype_name: '',
            normal_balance: 'debit',
            normal_balance_label: 'Debit',
        };

        if (insertAfterIndex !== undefined && insertAfterIndex >= 0 && insertAfterIndex < this.coaPreview.length) {
            this.coaPreview.splice(insertAfterIndex + 1, 0, newItem);
        } else {
            this.coaPreview.push(newItem);
        }
        this.updateSelectedCoaItems();
    },

    removeCoaItemRow(index) {
        if (index !== undefined && index >= 0 && index < this.coaPreview.length) {
            const item = this.coaPreview[index];
            if (item && item.isUserAdded) {
                this.coaPreview.splice(index, 1);
                this.updateSelectedCoaItems();
            }
        }
    },

    // Debounced account code generation
    debounceTimers: {},
    updateAccountCodeDebounced(item) {
        // Clear existing timeout for this item
        if (this.debounceTimers[item.id]) {
            clearTimeout(this.debounceTimers[item.id]);
        }

        // Set new timeout (3 seconds)
        this.debounceTimers[item.id] = setTimeout(() => {
            if (item.account_class_id && item.account_subclass_id) {
                item.account_code = this.generateAccountCodeFromItem(item);
                this.updateSelectedCoaItems();
            }
            delete this.debounceTimers[item.id];
        }, 3000);
    },

    // Update account code on blur (immediate)
    updateAccountCodeOnBlur(item) {
        // Clear any pending debounce for this item
        if (this.debounceTimers[item.id]) {
            clearTimeout(this.debounceTimers[item.id]);
            delete this.debounceTimers[item.id];
        }

        if (item.account_class_id && item.account_subclass_id) {
            item.account_code = this.generateAccountCodeFromItem(item);
            this.updateSelectedCoaItems();
        }
    },

    updateSelectedCoaItems() {
        this.selectedCoaItems = this.coaPreview
            .filter(item => item.account_name && item.account_class_id && item.account_subclass_id)
            .map(item => {
                const accountCode = item.account_code || this.generateAccountCodeFromItem(item);
                return {
                    coa_item_id: item.id && !item.id.startsWith('new-') ? item.id : null,
                    account_code: accountCode,
                    account_class: item.account_class_name || 'N/A',
                    account_subclass: item.account_subclass_name || 'N/A',
                    account_type: item.account_type_name || item.account_type_name || 'N/A',
                    account_subtype: item.account_subtype_name || 'N/A',
                    normal_balance: item.normal_balance || 'debit',
                    is_active: true,
                    // Store hierarchy IDs for new items
                    account_class_id: item.account_class_id,
                    account_subclass_id: item.account_subclass_id,
                    account_type_id: item.account_type_id,
                    account_type_name: item.account_type_name,
                    account_subtype_name: item.account_subtype_name,
                };
            });
    },

    generateAccountCodeFromItem(item) {
        if (!item.account_class_id || !item.account_subclass_id) {
            return item.account_code || '';
        }

        const classCodes = this.coaStructure.classCodes || {};
        const subclassOrders = this.coaStructure.subclassOrders || {};
        const typeOrders = this.coaStructure.typeOrders || {};
        const subtypeOrders = this.coaStructure.subtypeOrders || {};

        const classDigit = this.padNumber(classCodes[item.account_class_id] ?? 0, 1);
        const subclassDigit = this.padNumber(subclassOrders[item.account_subclass_id] ?? 1, 1);

        // For type, if we have an ID, use it; otherwise, use a default of 1
        // Note: custom typed types won't have orders, so they'll get default value
        const typeDigits = item.account_type_id
            ? this.padNumber(typeOrders[item.account_type_id] ?? 1, 2)
            : this.padNumber(1, 2);

        // For subtype, if we have an ID, use it; otherwise, use a default of 0
        // Note: custom typed subtypes won't have orders, so they'll get default value
        const subtypeDigits = item.account_subtype_id
            ? this.padNumber(subtypeOrders[item.account_subtype_id] ?? 0, 2)
            : this.padNumber(0, 2);

        return `${classDigit}${subclassDigit}${typeDigits}${subtypeDigits}`;
    },

    onClassChange(item, classId) {
        const selectedClass = this.accountClasses.find(c => c.id === classId);
        if (selectedClass) {
            item.account_class_id = selectedClass.id;
            item.account_class_name = selectedClass.name;
            item.account_subclass_id = '';
            item.account_subclass_name = '';
            item.account_type_id = '';
            item.account_type_name = '';
            item.account_subtype_name = '';
            if (item.account_class_id) {
                item.account_code = this.generateAccountCodeFromItem(item);
            }
            this.updateSelectedCoaItems();
        }
    },

    onSubclassChange(item, subclassId) {
        const selectedSubclass = this.accountSubclasses.find(s => s.id === subclassId);
        if (selectedSubclass) {
            item.account_subclass_id = selectedSubclass.id;
            item.account_subclass_name = selectedSubclass.name;
            item.account_type_id = '';
            item.account_type_name = '';
            item.account_subtype_name = '';
            if (item.account_class_id) {
                item.account_code = this.generateAccountCodeFromItem(item);
            }
            this.updateSelectedCoaItems();
        }
    },

    onTypeChange(item, typeId) {
        const selectedType = this.accountTypes.find(t => t.id === typeId);
        if (selectedType) {
            item.account_type_id = selectedType.id;
            item.account_type_name = selectedType.name;
            item.account_subtype_name = '';
            if (item.account_class_id) {
                item.account_code = this.generateAccountCodeFromItem(item);
            }
            this.updateSelectedCoaItems();
        }
    },

    onTypeInput(item, typeName) {
        if (typeName) {
            // Check if it matches an existing type
            const matchingType = this.accountTypes.find(t =>
                t.account_subclass_id === item.account_subclass_id &&
                t.name.toLowerCase() === typeName.toLowerCase()
            );

            if (matchingType) {
                item.account_type_id = matchingType.id;
                item.account_type_name = matchingType.name;
            } else {
                // Allow custom type name
                item.account_type_id = '';
                item.account_type_name = typeName;
            }
        } else {
            item.account_type_id = '';
            item.account_type_name = '';
        }
        if (item.account_class_id) {
            item.account_code = this.generateAccountCodeFromItem(item);
        }
        this.updateSelectedCoaItems();
    },

    getAvailableSubclasses(classId) {
        if (!classId) return [];
        return this.accountSubclasses.filter(s => s.account_class_id === classId);
    },

    getAvailableTypes(subclassId) {
        if (!subclassId) return [];
        return this.accountTypes.filter(t => t.account_subclass_id === subclassId);
    },

    collectSelectedTaxTypeIds() {
        const ids = [];
        // Iterate over taxSelections object keys to handle both array and single values
        Object.keys(this.taxSelections || {}).forEach(categoryId => {
            const selection = this.taxSelections[categoryId];
            if (Array.isArray(selection)) {
                selection.filter(Boolean).forEach(id => ids.push(String(id)));
            } else if (selection) {
                ids.push(String(selection));
            }
        });
        return Array.from(new Set(ids));
    },

    generateAccountCode(item) {
        const classCodes = this.coaStructure.classCodes || {};
        const subclassOrders = this.coaStructure.subclassOrders || {};
        const typeOrders = this.coaStructure.typeOrders || {};
        const subtypeOrders = this.coaStructure.subtypeOrders || {};

        const classDigit = this.padNumber(classCodes[item.account_class_id] ?? 0, 1);
        const subclassDigit = this.padNumber(subclassOrders[item.account_subclass_id] ?? 1, 1);
        const typeDigits = this.padNumber(typeOrders[item.account_type_id] ?? 1, 2);
        const subtypeDigits = this.padNumber(subtypeOrders[item.account_subtype_id] ?? 0, 2);
        return `${classDigit}${subclassDigit}${typeDigits}${subtypeDigits}`;
    },

    padNumber(value, size) {
        const numericValue = parseInt(value, 10);
        const safeValue = Number.isNaN(numericValue) ? 0 : numericValue;
        return String(safeValue).padStart(size, '0');
    },

    formatBalance(value) {
        const normalized = (value || 'debit').toLowerCase();
        return normalized.charAt(0).toUpperCase() + normalized.slice(1);
    },

    submitForm() {
        // OPTIMIZED: Batch all updates without await (no server round trips until submit)
        // This makes the submission instant instead of taking several minutes
        $wire.$set('business_name', this.business_name, false);
        $wire.$set('tin_number', this.tin_number, false);
        $wire.$set('business_email', this.business_email, false);
        $wire.$set('region_id', this.regionId, false);
        $wire.$set('province_id', this.provinceId, false);
        $wire.$set('city_id', this.cityId, false);
        $wire.$set('barangay_id', this.barangayId, false);
        $wire.$set('street_address', this.street_address, false);
        $wire.$set('building_name', this.building_name, false);
        $wire.$set('unit_number', this.unit_number, false);
        $wire.$set('postal_code', this.postal_code, false);
        $wire.$set('industry_type_ids', this.industry_type_ids, false);
        $wire.$set('fiscal_year_period_id', this.fiscal_year_period_id, false);
        $wire.$set('business_type_id', this.business_type_id, false);
        $wire.$set('government_agency_ids', this.government_agency_ids, false);
        $wire.$set('taxSelections', this.taxSelections, false);
        $wire.$set('selectedCoaItems', this.selectedCoaItems, false);

        // Submit once with all batched changes
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
                                                           x-model="taxSelections['{{ $categoryId }}']"
                                                           class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:checked:border-primary-600 dark:checked:bg-primary-600">
                                                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $taxTypeName }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="fi-input-wrp fi-fo-select">
                                            <div class="fi-input-wrp-content-ctn">
                                                <select x-model="taxSelections['{{ $categoryId }}']"
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


        <!-- Step 7: Chart of Accounts Preview -->
        <div x-show="currentStep === 7">
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                <div class="space-y-3">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Chart of Accounts Preview</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Based on your selections, these accounts will be connected to the business registration.</p>

                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" x-show="coaPreview.length > 0">
                                <thead class="bg-gray-50 dark:bg-gray-800/60">
                                    <tr>
                                        <th class="px-2 py-2 w-8"></th>
                                        <th class="px-2 py-2 w-8"></th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Account Code</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Account Name</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Class</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Subclass</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Type</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Subtype</th>
                                        <th class="px-2 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Normal Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    <template x-for="(item, index) in coaPreview" :key="item.id || index">
                                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors" x-data="{ hovered: false, parentScope: parentScope }" @mouseenter="hovered = true" @mouseleave="hovered = false">
                                            <!-- Plus Button (visible on hover) -->
                                            <td class="px-2 py-2 w-8" style="min-width: 32px;">
                                                <button type="button"
                                                        @click="parentScope ? parentScope.addCoaItemRow(index) : addCoaItemRow(index)"
                                                        :class="{ 'invisible': !hovered, 'visible': hovered }"
                                                        class="transition-opacity p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700 text-primary-600 dark:text-primary-400"
                                                        title="Add row below">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </button>
                                            </td>

                                            <!-- Delete Button (only for user-added items) -->
                                            <td class="px-2 py-2 w-8" style="min-width: 32px;">
                                                <button type="button"
                                                        @click="parentScope ? parentScope.removeCoaItemRow(index) : removeCoaItemRow(index)"
                                                        :class="{ 'invisible': !hovered || !item.isUserAdded, 'visible': hovered && item.isUserAdded }"
                                                        class="transition-opacity p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400"
                                                        title="Delete row">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>

                                            <!-- Account Code -->
                                            <td class="px-2 py-2">
                                                <span class="text-xs font-medium text-gray-900 dark:text-gray-100"
                                                      x-text="item.account_code || (item.account_class_id && item.account_subclass_id ? generateAccountCodeFromItem(item) : '-')"></span>
                                            </td>

                                            <!-- Account Name -->
                                            <td class="px-2 py-2">
                                                <input type="text"
                                                       x-model="item.account_name"
                                                       @input="item.account_code = item.account_class_id ? generateAccountCodeFromItem(item) : item.account_code; updateSelectedCoaItems();"
                                                       class="text-xs w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                                       placeholder="Enter account name">
                                            </td>

                                            <!-- Account Class (Dropdown only - disabled for system items) -->
                                            <td class="px-2 py-2">
                                                <template x-if="!item.isUserAdded">
                                                    <span class="text-xs text-gray-700 dark:text-gray-300" x-text="item.account_class_name || 'N/A'"></span>
                                                </template>
                                                <template x-if="item.isUserAdded">
                                                    <select x-model="item.account_class_id"
                                                            @change="onClassChange(item, item.account_class_id)"
                                                            class="text-xs w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                                                        <option value="">Select class</option>
                                                        <template x-for="ac in accountClasses" :key="ac.id">
                                                            <option :value="ac.id" x-text="ac.name"></option>
                                                        </template>
                                                    </select>
                                                </template>
                                            </td>

                                            <!-- Account Subclass (Dropdown only - disabled for system items) -->
                                            <td class="px-2 py-2">
                                                <template x-if="!item.isUserAdded">
                                                    <span class="text-xs text-gray-700 dark:text-gray-300" x-text="item.account_subclass_name || 'N/A'"></span>
                                                </template>
                                                <template x-if="item.isUserAdded">
                                                    <select x-model="item.account_subclass_id"
                                                            @change="onSubclassChange(item, item.account_subclass_id)"
                                                            :disabled="!item.account_class_id"
                                                            class="text-xs w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                                        <option value="">Select subclass</option>
                                                        <template x-for="sc in getAvailableSubclasses(item.account_class_id)" :key="sc.id">
                                                            <option :value="sc.id" x-text="sc.name"></option>
                                                        </template>
                                                    </select>
                                                </template>
                                            </td>

                                            <!-- Account Type (Combobox for user items, text for system items) -->
                                            <td class="px-2 py-2">
                                                <template x-if="!item.isUserAdded">
                                                    <span class="text-xs text-gray-700 dark:text-gray-300" x-text="item.account_type_name || 'N/A'"></span>
                                                </template>
                                                <template x-if="item.isUserAdded" x-data="{
                                                    item: item,
                                                    accountTypes: accountTypes,
                                                    parentScope: parentScope,
                                                    open: false,
                                                    searchText: '',
                                                    filteredOptions: [],
                                                    init() {
                                                        this.updateDisplay();
                                                        this.updateFilteredOptions();

                                                        this.$watch('item.account_subclass_id', () => {
                                                            this.updateFilteredOptions();
                                                            if (!this.item.account_subclass_id) {
                                                                this.item.account_type_id = '';
                                                                this.item.account_type_name = '';
                                                                this.searchText = '';
                                                            }
                                                        });

                                                        this.$watch('item.account_type_name', () => {
                                                            this.updateDisplay();
                                                            // Enable subtype input when type name is filled
                                                            // This is handled by the disabled condition on the subtype input
                                                        });

                                                        this.$watch('searchText', (value) => {
                                                            this.updateFilteredOptions();
                                                            if (value) {
                                                                const exactMatch = this.filteredOptions.find(t =>
                                                                    t.name.toLowerCase() === value.toLowerCase()
                                                                );
                                                                if (!exactMatch && this.item.account_subclass_id) {
                                                                    this.item.account_type_name = value;
                                                                    this.item.account_type_id = '';
                                                                    // Trigger debounced update
                                                                    if (this.parentScope && this.parentScope.updateAccountCodeDebounced) {
                                                                        this.parentScope.updateAccountCodeDebounced(this.item);
                                                                    }
                                                                }
                                                            } else {
                                                                this.item.account_type_name = '';
                                                                this.item.account_type_id = '';
                                                                if (this.item.account_class_id && this.item.account_subclass_id) {
                                                                    if (this.parentScope && this.parentScope.updateAccountCodeDebounced) {
                                                                        this.parentScope.updateAccountCodeDebounced(this.item);
                                                                    }
                                                                }
                                                            }
                                                        });
                                                    },
                                                    updateDisplay() {
                                                        if (this.item.account_type_id) {
                                                            const selected = this.accountTypes.find(t => t.id === this.item.account_type_id);
                                                            if (selected) {
                                                                this.searchText = selected.name;
                                                            }
                                                        } else if (this.item.account_type_name) {
                                                            this.searchText = this.item.account_type_name;
                                                        } else {
                                                            this.searchText = '';
                                                        }
                                                    },
                                                    updateFilteredOptions() {
                                                        if (!this.item.account_subclass_id) {
                                                            this.filteredOptions = [];
                                                            return;
                                                        }
                                                        this.filteredOptions = this.accountTypes.filter(t =>
                                                            t.account_subclass_id === this.item.account_subclass_id
                                                        );

                                                        if (this.searchText) {
                                                            this.filteredOptions = this.filteredOptions.filter(t =>
                                                                t.name.toLowerCase().includes(this.searchText.toLowerCase())
                                                            );
                                                        }
                                                    },
                                                    selectOption(option) {
                                                        if (this.parentScope && this.parentScope.onTypeChange) {
                                                            this.parentScope.onTypeChange(this.item, option.id);
                                                        }
                                                        this.searchText = option.name;
                                                        this.open = false;
                                                        // Immediately update account code when option is selected
                                                        if (this.item.account_class_id && this.item.account_subclass_id) {
                                                            if (this.parentScope && this.parentScope.generateAccountCodeFromItem) {
                                                                this.item.account_code = this.parentScope.generateAccountCodeFromItem(this.item);
                                                                if (this.parentScope.updateSelectedCoaItems) {
                                                                    this.parentScope.updateSelectedCoaItems();
                                                                }
                                                            }
                                                        }
                                                    }
                                                }" x-init="init()">
                                                    <div class="relative">
                                                        <input type="text"
                                                               x-model="searchText"
                                                               @click.stop="open = !open; if (!open) updateFilteredOptions();"
                                                               @focus="open = true; updateFilteredOptions();"
                                                               @keydown.escape="open = false"
                                                               @blur="setTimeout(() => { open = false; }, 200); if (parentScope && parentScope.updateAccountCodeOnBlur) parentScope.updateAccountCodeOnBlur(item)"
                                                               :disabled="!item.account_subclass_id"
                                                               class="text-xs w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                               placeholder="Type or select">
                                                        <div x-show="open && filteredOptions.length > 0"
                                                             x-transition:enter="transition ease-out duration-100"
                                                             x-transition:enter-start="transform opacity-0 scale-95"
                                                             x-transition:enter-end="transform opacity-100 scale-100"
                                                             x-transition:leave="transition ease-in duration-75"
                                                             x-transition:leave-start="transform opacity-100 scale-100"
                                                             x-transition:leave-end="transform opacity-0 scale-95"
                                                             @click.outside="open = false"
                                                             @click.stop
                                                             class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded shadow-lg max-h-48 overflow-y-auto">
                                                            <template x-for="option in filteredOptions" :key="option.id">
                                                                <div @click="selectOption(option); $event.stopPropagation();"
                                                                     :class="{ 'bg-gray-100 dark:bg-gray-700': item.account_type_id === option.id }"
                                                                     class="px-2 py-1 text-xs cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100"
                                                                     x-text="option.name"></div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </td>

                                            <!-- Account Subtype (Text input only - editable for both system and user items) -->
                                            <td class="px-2 py-2">
                                                <input type="text"
                                                       x-model="item.account_subtype_name"
                                                       @input="parentScope ? parentScope.updateAccountCodeDebounced(item) : updateAccountCodeDebounced(item); parentScope ? parentScope.updateSelectedCoaItems() : updateSelectedCoaItems();"
                                                       @blur="parentScope ? parentScope.updateAccountCodeOnBlur(item) : updateAccountCodeOnBlur(item)"
                                                       :disabled="!item.account_type_id && !item.account_type_name"
                                                       class="text-xs w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                       placeholder="Enter subtype">
                                            </td>

                                            <!-- Normal Balance -->
                                            <td class="px-2 py-2">
                                                <select x-model="item.normal_balance"
                                                        @change="item.normal_balance_label = formatBalance(item.normal_balance); updateSelectedCoaItems();"
                                                        class="text-xs w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                                                    <option value="debit">Debit</option>
                                                    <option value="credit">Credit</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
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
