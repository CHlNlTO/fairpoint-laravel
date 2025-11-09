<?php

namespace App\Livewire\COATemplateItems;

use App\Models\AccountClass;
use App\Models\AccountSubclass;
use App\Models\AccountSubtype;
use App\Models\AccountType;
use App\Models\COAItemBusinessType;
use App\Models\COAItemIndustryType;
use App\Models\COAItemTaxType;
use App\Models\COATemplateItem;
use Illuminate\Support\Facades\Log;

class EditCOAItem extends CreateCOAItems
{
    public string $mode = 'edit';
    public string $recordId;

    public function mount(string $recordId = null): void
    {
        if ($recordId === null) {
            throw new \InvalidArgumentException('Record ID is required for edit mode.');
        }

        $this->recordId = $recordId;

        $this->loadHierarchyData();

        $coaItem = COATemplateItem::with([
            'accountSubtype.accountType.accountSubclass.accountClass',
            'businessTypes',
            'industryTypes',
            'taxTypes',
        ])->findOrFail($recordId);

        $this->ignoreCoaItemIds = [$coaItem->id];

        $accountSubtype = $coaItem->accountSubtype;
        $accountType = $accountSubtype->accountType;
        $accountSubclass = $accountType->accountSubclass;
        $accountClass = $accountSubclass->accountClass;

        $this->ensureHierarchyOptionExists($accountClass, $accountSubclass, $accountType, $accountSubtype);

        $businessTypeIds = $coaItem->businessTypes->pluck('business_type_id')->toArray();
        $industryTypeIds = $coaItem->industryTypes->pluck('industry_type_id')->toArray();
        $taxTypeIds = $coaItem->taxTypes->pluck('tax_type_id')->toArray();

        $this->items = [[
            'id' => $coaItem->id,
            'account_code' => $coaItem->account_code,
            'account_name' => $coaItem->account_name,
            'account_class_id' => $accountClass->id,
            'account_class_name' => $accountClass->name,
            'account_subclass_id' => $accountSubclass->id,
            'account_subclass_name' => $accountSubclass->name,
            'account_type_id' => $accountType->id,
            'account_type_name' => $accountType->name,
            'account_subtype_id' => $accountSubtype->id,
            'account_subtype_name' => $accountSubtype->name,
            'normal_balance' => $coaItem->normal_balance,
            'is_active' => $coaItem->is_active,
            'is_default' => $coaItem->is_default,
            'business_type_ids' => $businessTypeIds,
            'industry_type_ids' => $industryTypeIds,
            'tax_type_ids' => $taxTypeIds,
            'tax_type_name' => '',
            'industry_type_name' => '',
            'business_type_name' => '',
            'needs_class_creation' => false,
            'needs_subclass_creation' => false,
            'needs_type_creation' => false,
            'needs_subtype_creation' => false,
        ]];
    }

    protected function ensureHierarchyOptionExists(
        AccountClass $accountClass,
        AccountSubclass $accountSubclass,
        AccountType $accountType,
        AccountSubtype $accountSubtype
    ): void {
        if (!collect($this->accountClasses)->contains('id', $accountClass->id)) {
            $this->accountClasses[] = [
                'id' => $accountClass->id,
                'code' => $accountClass->code,
                'name' => $accountClass->name,
            ];
        }

        if (!collect($this->accountSubclasses)->contains('id', $accountSubclass->id)) {
            $this->accountSubclasses[] = [
                'id' => $accountSubclass->id,
                'account_class_id' => $accountSubclass->account_class_id,
                'code' => $accountSubclass->code,
                'name' => $accountSubclass->name,
            ];
        }

        if (!collect($this->accountTypes)->contains('id', $accountType->id)) {
            $this->accountTypes[] = [
                'id' => $accountType->id,
                'account_subclass_id' => $accountType->account_subclass_id,
                'code' => $accountType->code,
                'name' => $accountType->name,
            ];
        }

        if (!collect($this->accountSubtypes)->contains('id', $accountSubtype->id)) {
            $this->accountSubtypes[] = [
                'id' => $accountSubtype->id,
                'account_type_id' => $accountSubtype->account_type_id,
                'code' => $accountSubtype->code,
                'name' => $accountSubtype->name,
                'class_code' => $accountSubtype->accountType->accountSubclass->accountClass->code,
                'subclass_code' => $accountSubtype->accountType->accountSubclass->code,
                'type_code' => $accountSubtype->accountType->code,
            ];
        }
    }

    public function save()
    {
        if (empty($this->items)) {
            return;
        }

        $item = &$this->items[0];

        Log::debug('Pre-update item snapshot', [
            'record_id' => $this->recordId,
            'item' => $item,
        ]);

        if (
            !empty($item['account_class_name']) &&
            (empty($item['account_class_id']) || ($item['needs_class_creation'] ?? false))
        ) {
            $item['account_class_id'] = $this->createOrGetAccountClass(
                $item['account_class_name'],
                $item['normal_balance'] ?? 'debit'
            );
        }

        if (!empty($item['account_subclass_name']) &&
            !empty($item['account_class_id']) &&
            (empty($item['account_subclass_id']) || ($item['needs_subclass_creation'] ?? false))) {
            $item['account_subclass_id'] = $this->createOrGetAccountSubclass(
                $item['account_subclass_name'],
                $item['account_class_id']
            );
        }

        if (!empty($item['account_type_name']) &&
            !empty($item['account_subclass_id']) &&
            (empty($item['account_type_id']) || ($item['needs_type_creation'] ?? false))) {
            $item['account_type_id'] = $this->createOrGetAccountType(
                $item['account_type_name'],
                $item['account_subclass_id']
            );
        }

        if (!empty($item['account_subtype_name']) &&
            !empty($item['account_type_id']) &&
            (empty($item['account_subtype_id']) || ($item['needs_subtype_creation'] ?? false))) {
            $item['account_subtype_id'] = $this->createOrGetAccountSubtype(
                $item['account_subtype_name'],
                $item['account_type_id']
            );
        }

        $this->ensureUniqueAccountCodes();

        $rules = [
            'items.0.account_name' => 'required|max:200',
            'items.0.account_class_id' => 'required',
            'items.0.account_subclass_id' => 'required',
            'items.0.account_type_id' => 'required',
            'items.0.account_subtype_id' => 'required',
            'items.0.account_code' => [
                'required',
                'size:6',
                function ($attribute, $value, $fail) {
                    $existsQuery = COATemplateItem::where('account_code', $value)
                        ->whereNotIn('id', [$this->recordId]);

                    if ($existsQuery->exists()) {
                        $fail("The account code {$value} already exists in the database.");
                    }
                },
            ],
            'items.0.normal_balance' => 'required|in:debit,credit',
        ];

        $this->validate($rules, [], [
            'items.0.account_name' => 'Account Name',
            'items.0.account_class_id' => 'Account Class',
            'items.0.account_subclass_id' => 'Account Subclass',
            'items.0.account_type_id' => 'Account Type',
            'items.0.account_subtype_id' => 'Account Subtype',
            'items.0.account_code' => 'Account Code',
            'items.0.normal_balance' => 'Normal Balance',
        ]);

        $coaItem = COATemplateItem::findOrFail($this->recordId);
        $coaItem->update([
            'account_code' => $item['account_code'],
            'account_name' => $item['account_name'],
            'account_subtype_id' => $item['account_subtype_id'],
            'normal_balance' => $item['normal_balance'],
            'is_active' => $item['is_active'] ?? true,
            'is_default' => $item['is_default'] ?? false,
        ]);

        COAItemBusinessType::where('account_item_id', $coaItem->id)->delete();
        foreach ($item['business_type_ids'] ?? [] as $businessTypeId) {
            COAItemBusinessType::create([
                'account_item_id' => $coaItem->id,
                'business_type_id' => $businessTypeId,
            ]);
        }

        COAItemIndustryType::where('account_item_id', $coaItem->id)->delete();
        foreach ($item['industry_type_ids'] ?? [] as $industryTypeId) {
            COAItemIndustryType::create([
                'account_item_id' => $coaItem->id,
                'industry_type_id' => $industryTypeId,
            ]);
        }

        COAItemTaxType::where('account_item_id', $coaItem->id)->delete();
        foreach ($item['tax_type_ids'] ?? [] as $taxTypeId) {
            COAItemTaxType::create([
                'account_item_id' => $coaItem->id,
                'tax_type_id' => $taxTypeId,
            ]);
        }

        session()->flash('success', 'Chart of Account item updated successfully.');

        return redirect()->route('filament.app.resources.chart-of-account-items.index');
    }
}
