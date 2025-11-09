<div class="space-y-6" x-data="{
    items: @entangle('items').live,
    accountClasses: @js($accountClasses),
    accountSubclasses: @js($accountSubclasses),
    accountTypes: @js($accountTypes),
    accountSubtypes: @js($accountSubtypes),
    businessTypes: @js($businessTypes),
    industryTypes: @js($industryTypes),
    taxTypes: @js($taxTypes),
    comboboxClass(item, index) {
        return {
            open: false,
            searchText: '',
            filteredOptions: this.accountClasses || [],
            selectedLabel: '',
            item: item,
            accountClasses: this.accountClasses,
            init() {
                // Use $nextTick to ensure item data is fully loaded
                this.$nextTick(() => {
                    this.updateDisplay();
                });

                this.$watch('item.account_class_id', () => {
                    this.updateDisplay();
                });
                this.$watch('item.account_class_name', () => {
                    this.updateDisplay();
                });
                this.$watch('searchText', (newValue) => {
                    if (newValue) {
                        this.filteredOptions = (this.accountClasses || []).filter(ac =>
                            ac.name.toLowerCase().includes(newValue.toLowerCase())
                        );
                        const exactMatch = (this.accountClasses || []).find(ac => ac.name.toLowerCase() === newValue.toLowerCase());
                        if (!exactMatch) {
                            this.item.account_class_name = newValue;
                            this.item.account_class_id = '';
                        }
                    } else {
                        this.filteredOptions = this.accountClasses || [];
                        this.item.account_class_name = '';
                        this.item.account_class_id = '';
                    }
                });
            },
            updateDisplay() {
                const selectedClass = (this.accountClasses || []).find(ac => ac.id === this.item.account_class_id);
                if (selectedClass) {
                    // ID is matched, display the matched option
                    this.selectedLabel = selectedClass.name;
                    this.searchText = selectedClass.name;
                } else if (this.item.account_class_name) {
                    // No ID but has a name (new item from CSV)
                    this.selectedLabel = this.item.account_class_name;
                    this.searchText = this.item.account_class_name;
                } else {
                    // Nothing selected
                    this.selectedLabel = '';
                    this.searchText = '';
                }
            },
            selectOption(option) {
                this.item.account_class_id = option.id;
                this.selectedLabel = option.name;
                this.searchText = option.name;
                this.item.account_class_name = option.name;
                this.item.account_subclass_id = '';
                this.item.account_subclass_name = '';
                this.item.account_type_id = '';
                this.item.account_type_name = '';
                this.item.account_subtype_id = '';
                this.item.account_subtype_name = '';
                this.item.account_code = '';
                this.open = false;
            }
        };
    },
    addItem() {
        this.items.push({
            account_code: '',
            account_name: '',
            account_class_id: '',
            account_class_name: '',
            account_subclass_id: '',
            account_subclass_name: '',
            account_type_id: '',
            account_type_name: '',
            account_subtype_id: '',
            account_subtype_name: '',
            normal_balance: 'debit',
            is_active: true,
            is_default: true,
            business_type_ids: [],
            industry_type_ids: [],
            tax_type_ids: [],
            tax_type_name: '',
            industry_type_name: '',
            business_type_name: '',
            needs_class_creation: false,
            needs_subclass_creation: false,
            needs_type_creation: false,
            needs_subtype_creation: false
        });
    },
    removeItem(index) {
        this.items.splice(index, 1);
    },
    toggleType(item, type, value) {
        // Get the appropriate array and the other two arrays
        let targetArray, otherArrays;
        if (type === 'business') {
            targetArray = item.business_type_ids;
            otherArrays = [item.industry_type_ids, item.tax_type_ids];
        } else if (type === 'industry') {
            targetArray = item.industry_type_ids;
            otherArrays = [item.business_type_ids, item.tax_type_ids];
        } else if (type === 'tax') {
            targetArray = item.tax_type_ids;
            otherArrays = [item.business_type_ids, item.industry_type_ids];
        }

        // Toggle the value
        const index = targetArray.indexOf(value);
        if (index > -1) {
            targetArray.splice(index, 1);
        } else {
            targetArray.push(value);
        }

        // Disable/enable other columns based on selections
        const hasSelections = targetArray.length > 0;
        otherArrays.forEach(arr => {
            if (hasSelections) {
                // Clear other arrays if this one has selections
                arr.splice(0, arr.length);
            }
        });
    },
    isDisabled(item, type) {
        if (type === 'business') {
            return item.industry_type_ids.length > 0 || item.tax_type_ids.length > 0;
        } else if (type === 'industry') {
            return item.business_type_ids.length > 0 || item.tax_type_ids.length > 0;
        } else if (type === 'tax') {
            return item.business_type_ids.length > 0 || item.industry_type_ids.length > 0;
        }
        return false;
    },
    isChecked(item, type, value) {
        if (type === 'business') {
            return item.business_type_ids.includes(value);
        } else if (type === 'industry') {
            return item.industry_type_ids.includes(value);
        } else if (type === 'tax') {
            return item.tax_type_ids.includes(value);
        }
        return false;
    }
}">
    <!-- CSV Import Section -->
    <div class="mb-6 p-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Import from CSV</h3>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <div class="fi-input-wrp fi-fo-file-upload">
                    <div class="fi-input-wrp-content-ctn">
                        <input type="file"
                               wire:model="csvFile"
                               accept=".csv"
                               class="fi-input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/20 dark:file:text-primary-400">
                    </div>
                </div>
                @error('csvFile')
                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror
            </div>
            <button wire:click="downloadTemplate"
                    wire:loading.attr="disabled"
                    wire:target="processCsv,save"
                    type="button"
                    class="inline-flex items-center justify-center gap-x-2 rounded-lg bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                Download Template
            </button>
            <button wire:click="processCsv"
                    wire:target="processCsv,save"
                    wire:loading.attr="disabled"
                    @disabled(!$csvFile)
                    type="button"
                    class="inline-flex items-center justify-center gap-x-2 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:opacity-60 disabled:cursor-not-allowed">
                <svg wire:loading wire:target="processCsv" class="h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span wire:loading.remove wire:target="processCsv">Process CSV</span>
                <span wire:loading wire:target="processCsv">Processing...</span>
            </button>
        </div>
    </div>

    <div class="fi-ac fi-align-end">
        <button wire:click="save"
                wire:target="save,processCsv"
                wire:loading.attr="disabled"
                type="button"
                class="inline-flex items-center justify-center gap-x-2 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:opacity-60 disabled:cursor-not-allowed">
            <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="save">Save All Items</span>
            <span wire:loading wire:target="save">Saving...</span>
        </button>
    </div>

    @if (session()->has('success'))
        <div class="rounded-lg bg-success-50 dark:bg-success-900/20 p-4 border border-success-200 dark:border-success-800">
            <p class="text-sm text-success-800 dark:text-success-200">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg bg-danger-50 dark:bg-danger-900/20 p-4 border border-danger-200 dark:border-danger-800">
            <ul class="list-disc list-inside text-sm text-danger-800 dark:text-danger-200">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="fi-fo-table-repeater fi-compact overflow-x-auto gap-64 scrollbar-thin pb-10">
        {{-- <div class="overflow-x-auto"> --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 100px;">Account Code</th>
                    <th>Account Name<sup class="fi-fo-table-repeater-header-required-mark">*</sup></th>
                    <th>Account Class<sup class="fi-fo-table-repeater-header-required-mark">*</sup></th>
                    <th>Account Subclass<sup class="fi-fo-table-repeater-header-required-mark">*</sup></th>
                    <th>Account Type<sup class="fi-fo-table-repeater-header-required-mark">*</sup></th>
                    <th>Account Subtype<sup class="fi-fo-table-repeater-header-required-mark">*</sup></th>
                    <th>Normal Balance<sup class="fi-fo-table-repeater-header-required-mark">*</sup></th>
                    <th>Business Type</th>
                    <th>Industry Type</th>
                    <th>Tax Type</th>
                    <th>Active</th>
                    <th>Default</th>
                    <th class="fi-fo-table-repeater-empty-header-cell"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in items" :key="index">
                    <tr>
                        <!-- Account Code -->
                        <td class="min-w-[100px]">
                            <div class="fi-input-wrp fi-disabled fi-fo-text-input">
                                <div class="fi-input-wrp-content-ctn">
                                    <input type="text"
                                           x-model="item.account_code"
                                           readonly
                                           disabled
                                           class="fi-input"
                                           placeholder="Auto">
                                </div>
                            </div>
                        </td>

                        <!-- Account Name -->
                        <td class="min-w-64">
                             <div class="fi-input-wrp fi-fo-text-input">
                                <div class="fi-input-wrp-content-ctn">
                                    <input type="text"
                                           x-model="item.account_name"
                                           class="fi-input"
                                           placeholder="Enter account name">
                                </div>
                            </div>
                        </td>

                        <!-- Account Class -->
                        <td class="min-w-48" x-data="comboboxClass(item, index)">
                            <div class="fi-input-wrp fi-fo-text-input">
                                <div class="fi-input-wrp-content-ctn">
                                    <div class="relative">
                                        <input type="text"
                                               x-model="searchText"
                                               @focus="open = true"
                                               @click="open = !open"
                                               @keydown.escape="open = false"
                                               class="fi-input w-full"
                                               placeholder="Type or select">
                                        <div x-show="open && filteredOptions.length > 0"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             @click.outside="open = false"
                                             class="fi-dropdown-panel fi-scrollable absolute z-100 w-full mt-1 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/20 max-h-60 overflow-y-auto">
                                            <ul class="fi-dropdown-list p-1">
                                                <template x-for="option in filteredOptions" :key="option.id">
                                                    <li @click="selectOption(option)"
                                                        :class="{ 'bg-gray-100 dark:bg-gray-700': item.account_class_id === option.id }"
                                                        class="fi-dropdown-list-item fi-select-input-option cursor-pointer px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <span x-text="option.name"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Account Subclass -->
                        <td class="min-w-48" x-data="{
                            open: false,
                            searchText: '',
                            filteredOptions: [],
                            selectedLabel: '',
                            getAvailableOptions() {
                                if (!this.item.account_class_id) return [];
                                const options = this.accountSubclasses.filter(s => s.account_class_id === this.item.account_class_id);
                                // If no options but we have a class name (new class), allow typing
                                return options;
                            },
                            init() {
                                // Use $nextTick to ensure item data is fully loaded
                                this.$nextTick(() => {
                                    this.updateDisplay();
                                    this.filteredOptions = this.getAvailableOptions();
                                });

                                this.$watch('item.account_class_id', () => {
                                    this.filteredOptions = this.getAvailableOptions();
                                    if (!this.item.account_class_id && !this.item.account_class_name) {
                                        this.item.account_subclass_id = '';
                                        this.item.account_subclass_name = '';
                                        this.item.account_type_id = '';
                                        this.item.account_type_name = '';
                                        this.item.account_subtype_id = '';
                                        this.item.account_subtype_name = '';
                                        this.item.account_code = '';
                                        this.searchText = '';
                                    }
                                });
                                this.$watch('item.account_class_name', () => {
                                    // When class name changes, update filtered options if we have an ID
                                    if (this.item.account_class_id) {
                                        this.filteredOptions = this.getAvailableOptions();
                                    }
                                });
                                this.$watch('item.account_subclass_id', () => {
                                    this.updateDisplay();
                                    if (!this.item.account_subclass_id) {
                                        this.item.account_type_id = '';
                                        this.item.account_type_name = '';
                                        this.item.account_subtype_id = '';
                                        this.item.account_subtype_name = '';
                                        this.item.account_code = '';
                                    }
                                });
                                this.$watch('item.account_subclass_name', () => {
                                    this.updateDisplay();
                                });
                                this.$watch('searchText', (newValue) => {
                                    if (newValue) {
                                        this.filteredOptions = this.getAvailableOptions().filter(s =>
                                            s.name.toLowerCase().includes(newValue.toLowerCase())
                                        );
                                        const exactMatch = this.getAvailableOptions().find(s => s.name.toLowerCase() === newValue.toLowerCase());
                                        if (!exactMatch && (this.item.account_class_id || this.item.account_class_name)) {
                                            this.item.account_subclass_name = newValue;
                                            this.item.account_subclass_id = '';
                                        }
                                    } else {
                                        this.filteredOptions = this.getAvailableOptions();
                                        this.item.account_subclass_name = '';
                                        this.item.account_subclass_id = '';
                                    }
                                });
                            },
                            updateDisplay() {
                                const selected = this.accountSubclasses.find(s => s.id === this.item.account_subclass_id);
                                if (selected) {
                                    this.selectedLabel = selected.name;
                                    this.searchText = selected.name;
                                } else if (this.item.account_subclass_name) {
                                    this.selectedLabel = this.item.account_subclass_name;
                                    this.searchText = this.item.account_subclass_name;
                                } else {
                                    this.selectedLabel = '';
                                    this.searchText = '';
                                }
                            },
                            selectOption(option) {
                                this.item.account_subclass_id = option.id;
                                this.selectedLabel = option.name;
                                this.searchText = option.name;
                                this.item.account_subclass_name = option.name;
                                this.item.account_type_id = '';
                                this.item.account_type_name = '';
                                this.item.account_subtype_id = '';
                                this.item.account_subtype_name = '';
                                this.item.account_code = '';
                                this.open = false;
                            }
                        }" x-init="init()">
                        <div class="fi-input-wrp fi-fo-text-input" :class="{ 'fi-disabled': !item.account_class_id && !item.account_class_name }">
                            <div class="fi-input-wrp-content-ctn">
                                <div class="relative">
                                    <input type="text"
                                           x-model="searchText"
                                           :disabled="!item.account_class_id && !item.account_class_name"
                                           @focus="item.account_class_id && (open = true)"
                                           @click="item.account_class_id && (open = !open)"
                                           @keydown.escape="open = false"
                                           class="fi-input w-full"
                                           placeholder="Type or select">
                                    <div x-show="open && filteredOptions.length > 0"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         @click.outside="open = false"
                                         class="fi-dropdown-panel fi-scrollable absolute z-100 w-full mt-1 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/20 max-h-60 overflow-y-auto">
                                        <ul class="fi-dropdown-list p-1">
                                            <template x-for="option in filteredOptions" :key="option.id">
                                                <li @click="selectOption(option)"
                                                    :class="{ 'bg-gray-100 dark:bg-gray-700': item.account_subclass_id === option.id }"
                                                    class="fi-dropdown-list-item fi-select-input-option cursor-pointer px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <span x-text="option.name"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </td>

                        <!-- Account Type -->
                        <td class="min-w-48" x-data="{
                            open: false,
                            searchText: '',
                            filteredOptions: [],
                            selectedLabel: '',
                            getAvailableOptions() {
                                if (!this.item.account_subclass_id) return [];
                                return this.accountTypes.filter(t => t.account_subclass_id === this.item.account_subclass_id);
                            },
                            init() {
                                // Use $nextTick to ensure item data is fully loaded
                                this.$nextTick(() => {
                                    this.updateDisplay();
                                    this.filteredOptions = this.getAvailableOptions();
                                });

                                this.$watch('item.account_subclass_id', () => {
                                    this.filteredOptions = this.getAvailableOptions();
                                    if (!this.item.account_subclass_id && !this.item.account_subclass_name) {
                                        this.item.account_type_id = '';
                                        this.item.account_type_name = '';
                                        this.item.account_subtype_id = '';
                                        this.item.account_subtype_name = '';
                                        this.item.account_code = '';
                                        this.searchText = '';
                                    }
                                });
                                this.$watch('item.account_subclass_name', () => {
                                    if (this.item.account_subclass_id) {
                                        this.filteredOptions = this.getAvailableOptions();
                                    }
                                });
                                this.$watch('item.account_type_id', () => {
                                    this.updateDisplay();
                                    if (!this.item.account_type_id) {
                                        this.item.account_subtype_id = '';
                                        this.item.account_subtype_name = '';
                                        this.item.account_code = '';
                                    }
                                });
                                this.$watch('item.account_type_name', () => {
                                    this.updateDisplay();
                                });
                                this.$watch('searchText', (newValue) => {
                                    if (newValue) {
                                        this.filteredOptions = this.getAvailableOptions().filter(t =>
                                            t.name.toLowerCase().includes(newValue.toLowerCase())
                                        );
                                        const exactMatch = this.getAvailableOptions().find(t => t.name.toLowerCase() === newValue.toLowerCase());
                                        if (!exactMatch && (this.item.account_subclass_id || this.item.account_subclass_name)) {
                                            this.item.account_type_name = newValue;
                                            this.item.account_type_id = '';
                                        }
                                    } else {
                                        this.filteredOptions = this.getAvailableOptions();
                                        this.item.account_type_name = '';
                                        this.item.account_type_id = '';
                                    }
                                });
                            },
                            updateDisplay() {
                                const selected = this.accountTypes.find(t => t.id === this.item.account_type_id);
                                if (selected) {
                                    this.selectedLabel = selected.name;
                                    this.searchText = selected.name;
                                } else if (this.item.account_type_name) {
                                    this.selectedLabel = this.item.account_type_name;
                                    this.searchText = this.item.account_type_name;
                                } else {
                                    this.selectedLabel = '';
                                    this.searchText = '';
                                }
                            },
                            selectOption(option) {
                                this.item.account_type_id = option.id;
                                this.selectedLabel = option.name;
                                this.searchText = option.name;
                                this.item.account_type_name = option.name;
                                this.item.account_subtype_id = '';
                                this.item.account_subtype_name = '';
                                this.item.account_code = '';
                                this.open = false;
                            }
                        }" x-init="init()">
                        <div class="fi-input-wrp fi-fo-text-input" :class="{ 'fi-disabled': !item.account_subclass_id && !item.account_subclass_name }">
                            <div class="fi-input-wrp-content-ctn">
                                <div class="relative">
                                    <input type="text"
                                           x-model="searchText"
                                           :disabled="!item.account_subclass_id && !item.account_subclass_name"
                                           @focus="item.account_subclass_id && (open = true)"
                                           @click="item.account_subclass_id && (open = !open)"
                                           @keydown.escape="open = false"
                                           class="fi-input w-full"
                                           placeholder="Type or select">
                                    <div x-show="open && filteredOptions.length > 0"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         @click.outside="open = false"
                                         class="fi-dropdown-panel fi-scrollable absolute z-100 w-full mt-1 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/20 max-h-60 overflow-y-auto">
                                        <ul class="fi-dropdown-list p-1">
                                            <template x-for="option in filteredOptions" :key="option.id">
                                                <li @click="selectOption(option)"
                                                    :class="{ 'bg-gray-100 dark:bg-gray-700': item.account_type_id === option.id }"
                                                    class="fi-dropdown-list-item fi-select-input-option cursor-pointer px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <span x-text="option.name"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </td>

                        <!-- Account Subtype -->
                        <td class="min-w-48" x-data="{
                            open: false,
                            searchText: '',
                            filteredOptions: [],
                            selectedLabel: '',
                            getAvailableOptions() {
                                if (!this.item.account_type_id) return [];
                                return this.accountSubtypes.filter(st => st.account_type_id === this.item.account_type_id);
                            },
                            init() {
                                // Use $nextTick to ensure item data is fully loaded
                                this.$nextTick(() => {
                                    this.updateDisplay();
                                    this.filteredOptions = this.getAvailableOptions();
                                });

                                this.$watch('item.account_type_id', () => {
                                    this.filteredOptions = this.getAvailableOptions();
                                    if (!this.item.account_type_id && !this.item.account_type_name) {
                                        this.item.account_subtype_id = '';
                                        this.item.account_subtype_name = '';
                                        this.item.account_code = '';
                                        this.searchText = '';
                                    }
                                });
                                this.$watch('item.account_type_name', () => {
                                    if (this.item.account_type_id) {
                                        this.filteredOptions = this.getAvailableOptions();
                                    }
                                });
                                this.$watch('item.account_subtype_id', () => {
                                    this.updateDisplay();
                                });
                                this.$watch('item.account_subtype_name', () => {
                                    this.updateDisplay();
                                });
                                this.$watch('searchText', (newValue) => {
                                    if (newValue) {
                                        this.filteredOptions = this.getAvailableOptions().filter(st =>
                                            st.name.toLowerCase().includes(newValue.toLowerCase())
                                        );
                                        const exactMatch = this.getAvailableOptions().find(st => st.name.toLowerCase() === newValue.toLowerCase());
                                        if (!exactMatch && (this.item.account_type_id || this.item.account_type_name)) {
                                            this.item.account_subtype_name = newValue;
                                            this.item.account_subtype_id = '';
                                            this.item.account_code = '';
                                        }
                                    } else {
                                        this.filteredOptions = this.getAvailableOptions();
                                        this.item.account_subtype_name = '';
                                        this.item.account_subtype_id = '';
                                        this.item.account_code = '';
                                    }
                                });
                            },
                            updateDisplay() {
                                const selected = this.accountSubtypes.find(st => st.id === this.item.account_subtype_id);
                                if (selected) {
                                    this.selectedLabel = selected.name;
                                    this.searchText = selected.name;
                                } else if (this.item.account_subtype_name) {
                                    this.selectedLabel = this.item.account_subtype_name;
                                    this.searchText = this.item.account_subtype_name;
                                } else {
                                    this.selectedLabel = '';
                                    this.searchText = '';
                                }
                            },
                            selectOption(option) {
                                this.item.account_subtype_id = option.id;
                                this.selectedLabel = option.name;
                                this.searchText = option.name;
                                this.item.account_subtype_name = option.name;
                                this.item.account_code = '';
                                this.open = false;
                                $wire.generateAccountCode(index);
                            }
                        }" x-init="init()">
                        <div class="fi-input-wrp fi-fo-text-input" :class="{ 'fi-disabled': !item.account_type_id && !item.account_type_name }">
                            <div class="fi-input-wrp-content-ctn">
                                <div class="relative">
                                    <input type="text"
                                           x-model="searchText"
                                           :disabled="!item.account_type_id && !item.account_type_name"
                                           @focus="item.account_type_id && (open = true)"
                                           @click="item.account_type_id && (open = !open)"
                                           @keydown.escape="open = false"
                                           class="fi-input w-full"
                                           placeholder="Type or select">
                                    <div x-show="open && filteredOptions.length > 0"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         @click.outside="open = false"
                                         class="fi-dropdown-panel fi-scrollable absolute z-100 w-full mt-1 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/20 max-h-60 overflow-y-auto">
                                        <ul class="fi-dropdown-list p-1">
                                            <template x-for="option in filteredOptions" :key="option.id">
                                                <li @click="selectOption(option)"
                                                    :class="{ 'bg-gray-100 dark:bg-gray-700': item.account_subtype_id === option.id }"
                                                    class="fi-dropdown-list-item fi-select-input-option cursor-pointer px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <span x-text="option.name"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </td>

                        <!-- Normal Balance -->
                        <td class="min-w-40" x-data="{
                            open: false,
                            options: [
                                { value: 'debit', label: 'Debit' },
                                { value: 'credit', label: 'Credit' }
                            ],
                            selectedLabel: '',
                            init() {
                                const selected = this.options.find(o => o.value === this.item.normal_balance);
                                if (selected) {
                                    this.selectedLabel = selected.label;
                                }
                                this.$watch('item.normal_balance', (newValue) => {
                                    const selected = this.options.find(o => o.value === newValue);
                                    this.selectedLabel = selected ? selected.label : '';
                                });
                            }
                        }" x-init="init()">
                        <div class="fi-input-wrp fi-fo-select">
                            <div class="fi-input-wrp-content-ctn">
                                <div class="fi-select-input">
                                    <div class="fi-select-input-ctn relative" @click.outside="open = false">
                                        <button type="button"
                                                @click="open = !open"
                                                class="fi-select-input-btn w-full text-left">
                                            <div class="fi-select-input-value-ctn">
                                                <span x-show="!item.normal_balance" class="fi-select-input-placeholder">Select an option</span>
                                                <span x-show="item.normal_balance" x-text="selectedLabel"></span>
                                            </div>
                                        </button>
                                        <div x-show="open"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="fi-dropdown-panel fi-scrollable absolute z-100 w-full mt-1 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/20">
                                            <ul class="fi-dropdown-list p-1">
                                                <template x-for="option in options" :key="option.value">
                                                    <li @click="item.normal_balance = option.value; open = false;"
                                                        :class="{ 'bg-gray-100 dark:bg-gray-700': item.normal_balance === option.value }"
                                                        class="fi-dropdown-list-item fi-select-input-option cursor-pointer">
                                                        <span x-text="option.label"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </td>

                        <!-- Business Type -->
                        <td class="min-w-56">
                            <div class="fi-input-wrp" :class="{ 'fi-disabled': isDisabled(item, 'business') }">
                                <div class="space-y-1 max-h-32 overflow-y-auto p-1">
                                    <template x-for="bt in businessTypes" :key="bt.id">
                                        <label class="flex items-center gap-x-2 cursor-pointer"
                                               :class="{ 'opacity-50 cursor-not-allowed': isDisabled(item, 'business') }">
                                            <input type="checkbox"
                                                   :checked="isChecked(item, 'business', bt.id)"
                                                   :disabled="isDisabled(item, 'business')"
                                                   @click="toggleType(item, 'business', bt.id)"
                                                   class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                                            <span class="text-sm" x-text="bt.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </td>

                        <!-- Industry Type -->
                        <td class="min-w-56">
                            <div class="fi-input-wrp" :class="{ 'fi-disabled': isDisabled(item, 'industry') }">
                                <div class="space-y-1 max-h-32 overflow-y-auto p-1">
                                    <template x-for="it in industryTypes" :key="it.id">
                                        <label class="flex items-center gap-x-2 cursor-pointer"
                                               :class="{ 'opacity-50 cursor-not-allowed': isDisabled(item, 'industry') }">
                                            <input type="checkbox"
                                                   :checked="isChecked(item, 'industry', it.id)"
                                                   :disabled="isDisabled(item, 'industry')"
                                                   @click="toggleType(item, 'industry', it.id)"
                                                   class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                                            <span class="text-sm" x-text="it.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </td>

                        <!-- Tax Type -->
                        <td class="min-w-56">
                            <div class="fi-input-wrp" :class="{ 'fi-disabled': isDisabled(item, 'tax') }">
                                <div class="space-y-1 max-h-32 overflow-y-auto p-1">
                                    <template x-for="tt in taxTypes" :key="tt.id">
                                        <label class="flex items-center gap-x-2 cursor-pointer"
                                               :class="{ 'opacity-50 cursor-not-allowed': isDisabled(item, 'tax') }">
                                            <input type="checkbox"
                                                   :checked="isChecked(item, 'tax', tt.id)"
                                                   :disabled="isDisabled(item, 'tax')"
                                                   @click="toggleType(item, 'tax', tt.id)"
                                                   class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50">
                                            <span class="text-sm" x-text="tt.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </td>

                        <!-- Is Active -->
                        <td class="min-w-[80px]">
                            <div class="col-span-1 flex items-center justify-center fi-fo-field-content-col">
                                <button x-bind:aria-checked="item.is_active.toString()"
                                        x-on:click="item.is_active = !item.is_active"
                                        x-bind:class="
                                            item.is_active ? 'fi-toggle-on fi-color fi-color-primary fi-bg-color-600 fi-text-color-600 dark:fi-bg-color-500' : 'fi-toggle-off'
                                        "
                                        class="fi-toggle fi-fo-toggle"
                                        role="switch"
                                        type="button">
                                    <div>
                                        <div aria-hidden="true">

                                        </div>

                                        <div aria-hidden="true">

                                        </div>
                                    </div>
                                </button>
                            </div>
                        </td>

                        <!-- Is Default -->
                        <td class="min-w-[80px]">
                            <div class="col-span-1 flex items-center justify-center fi-fo-field-content-col">
                                <button x-bind:aria-checked="item.is_default.toString()"
                                        x-on:click="item.is_default = !item.is_default"
                                        x-bind:class="
                                            item.is_default ? 'fi-toggle-on fi-color fi-color-primary fi-bg-color-600 fi-text-color-600 dark:fi-bg-color-500' : 'fi-toggle-off'
                                        "
                                        class="fi-toggle fi-fo-toggle"
                                        role="switch"
                                        type="button">
                                    <div>
                                        <div aria-hidden="true">

                                        </div>

                                        <div aria-hidden="true"></div>

                                        </div>
                                    </div>
                                </button>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td>
                            <div class="fi-fo-table-repeater-actions">
                                <button type="button"
                                        @click="removeItem(index)"
                                        class="fi-color-danger fi-icon-btn fi-size-sm fi-ac-icon-btn-action"
                                        title="Delete">
                                    <svg class="fi-icon fi-size-md" xmlns="http://www.w.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                      <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        {{-- </div> --}}

        <!-- Add Item Button -->
        <div class="fi-fo-table-repeater-add">
            <button type="button"
                    @click="addItem()"
                    class="fi-btn fi-size-sm fi-ac-btn-action">
                + Add Another Item
            </button>
        </div>
    </div>
</div>
