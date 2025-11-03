<?php

namespace App\Livewire\COATemplateItems;

use App\Models\AccountClass;
use App\Models\AccountSubclass;
use App\Models\AccountSubtype;
use App\Models\AccountType;
use App\Models\BusinessType;
use App\Models\COAItemBusinessType;
use App\Models\COAItemIndustryType;
use App\Models\COAItemTaxType;
use App\Models\COATemplateItem;
use App\Models\IndustryType;
use App\Models\TaxType;
use Livewire\Component;

class CreateCOAItems extends Component
{
    public $items = [];

    // Hierarchy data loaded once
    public $accountClasses = [];
    public $accountSubclasses = [];
    public $accountTypes = [];
    public $accountSubtypes = [];
    public $businessTypes = [];
    public $industryTypes = [];
    public $taxTypes = [];

    public function mount()
    {
        // Load all hierarchy data once
        $this->loadHierarchyData();

        // Initialize with 1 empty item
        $this->items = [[
            'account_code' => '',
            'account_name' => '',
            'account_class_id' => '',
            'account_subclass_id' => '',
            'account_type_id' => '',
            'account_subtype_id' => '',
            'normal_balance' => 'debit',
            'is_active' => true,
            'is_default' => true,
            'business_type_ids' => [],
            'industry_type_ids' => [],
            'tax_type_ids' => [],
        ]];
    }

    public function loadHierarchyData()
    {
        // Load all data in one go with relationships
        $this->accountClasses = AccountClass::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'code' => $class->code,
                    'name' => $class->name,
                ];
            })
            ->toArray();

        $this->accountSubclasses = AccountSubclass::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($subclass) {
                return [
                    'id' => $subclass->id,
                    'account_class_id' => $subclass->account_class_id,
                    'code' => $subclass->code,
                    'name' => $subclass->name,
                ];
            })
            ->toArray();

        $this->accountTypes = AccountType::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'account_subclass_id' => $type->account_subclass_id,
                    'code' => $type->code,
                    'name' => $type->name,
                ];
            })
            ->toArray();

        $this->accountSubtypes = AccountSubtype::with(['accountType.accountSubclass.accountClass'])
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($subtype) {
                return [
                    'id' => $subtype->id,
                    'account_type_id' => $subtype->account_type_id,
                    'code' => $subtype->code,
                    'name' => $subtype->name,
                    'class_code' => $subtype->accountType->accountSubclass->accountClass->code,
                    'subclass_code' => $subtype->accountType->accountSubclass->code,
                    'type_code' => $subtype->accountType->code,
                ];
            })
            ->toArray();

        $this->businessTypes = BusinessType::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                ];
            })
            ->toArray();

        $this->industryTypes = IndustryType::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                ];
            })
            ->toArray();

        $this->taxTypes = TaxType::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                ];
            })
            ->toArray();
    }

    public function generateAccountCode($index)
    {
        if (!isset($this->items[$index]['account_subtype_id']) || !$this->items[$index]['account_subtype_id']) {
            $this->items[$index]['account_code'] = '';
            return;
        }

        $subtypeId = $this->items[$index]['account_subtype_id'];
        $subtype = collect($this->accountSubtypes)->firstWhere('id', $subtypeId);

        if (!$subtype) {
            return;
        }

        // Generate base code
        $classCode = str_pad((string)$subtype['class_code'], 1, '0', STR_PAD_LEFT);
        $subclassCode = str_pad((string)$subtype['subclass_code'], 1, '0', STR_PAD_LEFT);
        $typeCode = str_pad((string)$subtype['type_code'], 2, '0', STR_PAD_LEFT);

        // Get existing codes from database and current form
        $existingCodes = COATemplateItem::where('account_subtype_id', $subtypeId)
            ->pluck('account_code')
            ->toArray();

        // Also check codes from current form items
        foreach ($this->items as $item) {
            if (isset($item['account_code']) && $item['account_code'] && $item['account_subtype_id'] === $subtypeId) {
                $existingCodes[] = $item['account_code'];
            }
        }

        // Find highest last two digits
        $highestCode = 0;
        foreach ($existingCodes as $code) {
            if (strlen($code) < 2) continue;
            $lastTwo = substr($code, -2);
            if (is_numeric($lastTwo) && (int)$lastTwo > $highestCode) {
                $highestCode = (int)$lastTwo;
            }
        }

        $nextSubtypeCode = str_pad($highestCode + 1, 2, '0', STR_PAD_LEFT);

        $this->items[$index]['account_code'] = $classCode . $subclassCode . $typeCode . $nextSubtypeCode;
    }

    public function save()
    {
        // Validate all items
        $rules = [];
        foreach ($this->items as $index => $item) {
            $rules["items.{$index}.account_name"] = 'required|max:200';
            $rules["items.{$index}.account_class_id"] = 'required';
            $rules["items.{$index}.account_subclass_id"] = 'required';
            $rules["items.{$index}.account_type_id"] = 'required';
            $rules["items.{$index}.account_subtype_id"] = 'required';
            $rules["items.{$index}.account_code"] = 'required|size:6';
            $rules["items.{$index}.normal_balance"] = 'required|in:debit,credit';
        }

        $this->validate($rules);

        // Create all items
        foreach ($this->items as $item) {
            $coaItem = COATemplateItem::create([
                'account_code' => $item['account_code'],
                'account_name' => $item['account_name'],
                'account_subtype_id' => $item['account_subtype_id'],
                'normal_balance' => $item['normal_balance'],
                'is_active' => $item['is_active'] ?? true,
                'is_default' => $item['is_default'] ?? true,
            ]);

            // Create pivot table entries for business types
            if (!empty($item['business_type_ids'])) {
                foreach ($item['business_type_ids'] as $businessTypeId) {
                    COAItemBusinessType::create([
                        'account_item_id' => $coaItem->id,
                        'business_type_id' => $businessTypeId,
                    ]);
                }
            }

            // Create pivot table entries for industry types
            if (!empty($item['industry_type_ids'])) {
                foreach ($item['industry_type_ids'] as $industryTypeId) {
                    COAItemIndustryType::create([
                        'account_item_id' => $coaItem->id,
                        'industry_type_id' => $industryTypeId,
                    ]);
                }
            }

            // Create pivot table entries for tax types
            if (!empty($item['tax_type_ids'])) {
                foreach ($item['tax_type_ids'] as $taxTypeId) {
                    COAItemTaxType::create([
                        'account_item_id' => $coaItem->id,
                        'tax_type_id' => $taxTypeId,
                    ]);
                }
            }
        }

        session()->flash('success', 'Chart of Account items created successfully.');

        return redirect()->route('filament.app.resources.chart-of-account-items.index');
    }

    public function render()
    {
        return view('livewire.coa-template-items.create-coa-items');
    }
}
