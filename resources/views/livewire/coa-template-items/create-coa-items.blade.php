<div class="space-y-6" x-data="{
    items: @entangle('items').live,
    accountClasses: @js($accountClasses),
    accountSubclasses: @js($accountSubclasses),
    accountTypes: @js($accountTypes),
    accountSubtypes: @js($accountSubtypes),
    addItem() {
        this.items.push({
            account_code: '',
            account_name: '',
            account_class_id: '',
            account_subclass_id: '',
            account_type_id: '',
            account_subtype_id: '',
            normal_balance: 'debit',
            is_active: true,
            is_default: true
        });
    },
    removeItem(index) {
        this.items.splice(index, 1);
    }
}">
    <div class="fi-ac fi-align-end">
        <button wire:click="save" type="button"
                class="inline-flex items-center justify-center gap-x-2 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
            Save All Items
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

    <div class="fi-fo-table-repeater fi-compact">
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
                    <th>Active</th>
                    <th>Default</th>
                    <th class="fi-fo-table-repeater-empty-header-cell"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in items" :key="index">
                    <tr>
                        <!-- Account Code -->
                        <td>
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
                        <td>
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
                        <td x-data="{
                            open: false,
                            selectedLabel: '',
                            init() {
                                const selectedClass = this.accountClasses.find(ac => ac.id === this.item.account_class_id);
                                if (selectedClass) {
                                    this.selectedLabel = selectedClass.name;
                                }
                                this.$watch('item.account_class_id', (newValue) => {
                                    const selected = this.accountClasses.find(ac => ac.id === newValue);
                                    this.selectedLabel = selected ? selected.name : '';
                                });
                            }
                        }" x-init="init()">
                        <div class="fi-input-wrp fi-fo-select">
                            <div class="fi-input-wrp-content-ctn">
                                <div class="fi-select-input">
                                    <div class="fi-select-input-ctn" @click.outside="open = false">
                                        <!-- This is the visible button that looks like an input -->
                                        <button type="button"
                                                @click="open = !open"
                                                class="fi-select-input-btn w-full text-left">
                                            <div class="fi-select-input-value-ctn">
                                                <!-- Show placeholder if no value is selected -->
                                                <span x-show="!item.account_class_id" class="fi-select-input-placeholder">
                                                    Select an option
                                                </span>
                                                <!-- Show selected value label if a value exists -->
                                                <span x-show="item.account_class_id" x-text="selectedLabel"></span>
                                            </div>
                                        </button>

                                        <!-- This is the custom dropdown panel -->
                                        <div x-show="open"
                                            x-transition
                                            class="fi-dropdown-panel fi-scrollable absolute z-10 w-full mt-1 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/20"
                                            style="display: none;">
                                            <ul class="fi-dropdown-list p-1">
                                                <template x-for="aclass in accountClasses" :key="aclass.id">
                                                    <li @click="item.account_class_id = aclass.id; open = false;"
                                                        :class="{ 'bg-gray-100 dark:bg-gray-700': item.account_class_id === aclass.id }"
                                                        class="fi-dropdown-list-item fi-select-input-option cursor-pointer">
                                                        <span x-text="aclass.name"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </td>

                        <!-- Account Subclass -->
                        <td x-data="{
                            open: false,
                            selectedLabel: '',
                            init() {
                                const selected = this.accountSubclasses.find(s => s.id === this.item.account_subclass_id);
                                if (selected) {
                                    this.selectedLabel = selected.name;
                                }
                                this.$watch('item.account_subclass_id', (newValue) => {
                                    const selected = this.accountSubclasses.find(s => s.id === newValue);
                                    this.selectedLabel = selected ? selected.name : '';
                                    if (!newValue) { // Reset dependent fields when this one is cleared
                                        this.item.account_type_id = '';
                                        this.item.account_subtype_id = '';
                                    }
                                });
                            }
                        }" x-init="init()">
                        <div class="fi-input-wrp fi-fo-select" :class="{ 'fi-disabled': !item.account_class_id }">
                            <div class="fi-input-wrp-content-ctn">
                                <div class="fi-select-input">
                                    <div class="fi-select-input-ctn" @click.outside="open = false">
                                        <button type="button"
                                                @click="item.account_class_id && (open = !open)"
                                                :disabled="!item.account_class_id"
                                                class="fi-select-input-btn w-full text-left">
                                            <div class="fi-select-input-value-ctn">
                                                <span x-show="!item.account_subclass_id" class="fi-select-input-placeholder">Select an option</span>
                                                <span x-show="item.account_subclass_id" x-text="selectedLabel"></span>
                                            </div>
                                        </button>
                                        <div x-show="open" x-transition class="fi-dropdown-panel fi-scrollable absolute z-10 w-full mt-1 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/20" style="display: none;">
                                            <ul class="fi-dropdown-list p-1">
                                                <template x-for="subclass in accountSubclasses.filter(s => s.account_class_id === item.account_class_id)" :key="subclass.id">
                                                    <li @click="item.account_subclass_id = subclass.id; item.account_type_id = ''; item.account_subtype_id = ''; item.account_code = ''; open = false;"
                                                        :class="{ 'bg-gray-100 dark:bg-gray-700': item.account_subclass_id === subclass.id }"
                                                        class="fi-dropdown-list-item fi-select-input-option cursor-pointer">
                                                        <span x-text="subclass.name"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </td>

                        <!-- Account Type -->
                        <td x-data="{
                            open: false,
                            selectedLabel: '',
                            init() {
                                const selected = this.accountTypes.find(t => t.id === this.item.account_type_id);
                                if (selected) {
                                    this.selectedLabel = selected.name;
                                }
                                this.$watch('item.account_type_id', (newValue) => {
                                    const selected = this.accountTypes.find(t => t.id === newValue);
                                    this.selectedLabel = selected ? selected.name : '';
                                    if (!newValue) { // Reset dependent fields when this one is cleared
                                        this.item.account_subtype_id = '';
                                    }
                                });
                            }
                        }" x-init="init()">
                        <div class="fi-input-wrp fi-fo-select" :class="{ 'fi-disabled': !item.account_subclass_id }">
                            <div class="fi-input-wrp-content-ctn">
                                <div class="fi-select-input">
                                    <div class="fi-select-input-ctn" @click.outside="open = false">
                                        <button type="button"
                                                @click="item.account_subclass_id && (open = !open)"
                                                :disabled="!item.account_subclass_id"
                                                class="fi-select-input-btn w-full text-left">
                                            <div class="fi-select-input-value-ctn">
                                                <span x-show="!item.account_type_id" class="fi-select-input-placeholder">Select an option</span>
                                                <span x-show="item.account_type_id" x-text="selectedLabel"></span>
                                            </div>
                                        </button>
                                        <div x-show="open" x-transition class="fi-dropdown-panel fi-scrollable absolute z-10 w-full mt-1 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/20" style="display: none;">
                                            <ul class="fi-dropdown-list p-1">
                                                <template x-for="type in accountTypes.filter(t => t.account_subclass_id === item.account_subclass_id)" :key="type.id">
                                                    <li @click="item.account_type_id = type.id; item.account_subtype_id = ''; item.account_code = ''; open = false;"
                                                        :class="{ 'bg-gray-100 dark:bg-gray-700': item.account_type_id === type.id }"
                                                        class="fi-dropdown-list-item fi-select-input-option cursor-pointer">
                                                        <span x-text="type.name"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </td>

                        <!-- Account Subtype -->
                        <td x-data="{
                            open: false,
                            selectedLabel: '',
                            init() {
                                const selected = this.accountSubtypes.find(st => st.id === this.item.account_subtype_id);
                                if (selected) {
                                    this.selectedLabel = selected.name;
                                }
                                this.$watch('item.account_subtype_id', (newValue) => {
                                    const selected = this.accountSubtypes.find(st => st.id === newValue);
                                    this.selectedLabel = selected ? selected.name : '';
                                });
                            }
                        }" x-init="init()">
                        <div class="fi-input-wrp fi-fo-select" :class="{ 'fi-disabled': !item.account_type_id }">
                            <div class="fi-input-wrp-content-ctn">
                                <div class="fi-select-input">
                                    <div class="fi-select-input-ctn" @click.outside="open = false">
                                        <button type="button"
                                                @click="item.account_type_id && (open = !open)"
                                                :disabled="!item.account_type_id"
                                                class="fi-select-input-btn w-full text-left">
                                            <div class="fi-select-input-value-ctn">
                                                <span x-show="!item.account_subtype_id" class="fi-select-input-placeholder">Select an option</span>
                                                <span x-show="item.account_subtype_id" x-text="selectedLabel"></span>
                                            </div>
                                        </button>
                                        <div x-show="open" x-transition class="fi-dropdown-panel fi-scrollable absolute z-10 w-full mt-1 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/20" style="display: none;">
                                            <ul class="fi-dropdown-list p-1">
                                                <template x-for="subtype in accountSubtypes.filter(st => st.account_type_id === item.account_type_id)" :key="subtype.id">
                                                    <li @click="item.account_subtype_id = subtype.id; open = false; $wire.generateAccountCode(index)"
                                                        :class="{ 'bg-gray-100 dark:bg-gray-700': item.account_subtype_id === subtype.id }"
                                                        class="fi-dropdown-list-item fi-select-input-option cursor-pointer">
                                                        <span x-text="subtype.name"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </td>

                        <!-- Normal Balance -->
                        <td x-data="{
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
                                    <div class="fi-select-input-ctn" @click.outside="open = false">
                                        <button type="button"
                                                @click="open = !open"
                                                class="fi-select-input-btn w-full text-left">
                                            <div class="fi-select-input-value-ctn">
                                                <span x-show="!item.normal_balance" class="fi-select-input-placeholder">Select an option</span>
                                                <span x-show="item.normal_balance" x-text="selectedLabel"></span>
                                            </div>
                                        </button>
                                        <div x-show="open" x-transition class="fi-dropdown-panel fi-scrollable absolute z-10 w-full mt-1 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/20" style="display: none;">
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

                        <!-- Is Active -->
                        <td>
                            <div class="col-span-1 flex items-center justify-center fi-fo-field-content-col">
                                <button x-data="{ state: $wire.$entangle('item.is_active', false) }" x-bind:aria-checked="state?.toString()" x-on:click="state = ! state" x-bind:class="
                                        state ? 'fi-toggle-on fi-color fi-color-primary fi-bg-color-600 fi-text-color-600 dark:fi-bg-color-500' : 'fi-toggle-off'    " class="fi-toggle fi-fo-toggle" role="switch" type="button" ariachecked="false" id="form.is_default" wire:loading.attr="disabled" wire:target="data.is_default" aria-checked="true">
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
                        <td>
                            <div class="col-span-1 flex items-center justify-center fi-fo-field-content-col">
                                <button x-data="{ state: $wire.$entangle('item.is_default', false) }" x-bind:aria-checked="state?.toString()" x-on:click="state = ! state" x-bind:class="
                                        state ? 'fi-toggle-on fi-color fi-color-primary fi-bg-color-600 fi-text-color-600 dark:fi-bg-color-500' : 'fi-toggle-off'    " class="fi-toggle fi-fo-toggle" role="switch" type="button" ariachecked="false" id="form.is_default" wire:loading.attr="disabled" wire:target="data.is_default" aria-checked="true">
                                    <div>
                                        <div aria-hidden="true">

                                        </div>

                                        <div aria-hidden="true">

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
