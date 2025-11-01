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
use Livewire\Component;

class CreateCOAItems extends Component
{
    public $items = [];

    // Hierarchy data loaded once
    public $accountClasses = [];
    public $accountSubclasses = [];
    public $accountTypes = [];
    public $accountSubtypes = [];

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
            'is_default' => false,
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
    }

    public function addItem()
    {
        $this->items[] = [
            'account_code' => '',
            'account_name' => '',
            'account_class_id' => '',
            'account_subclass_id' => '',
            'account_type_id' => '',
            'account_subtype_id' => '',
            'normal_balance' => 'debit',
            'is_active' => true,
            'is_default' => false,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Re-index array
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
                'is_default' => $item['is_default'] ?? false,
            ]);

            // Create pivot table entries if needed (business_types, industry_types, tax_types)
            // Add these if you have the relationships
        }

        session()->flash('success', 'Chart of Account items created successfully.');

        return redirect()->route('filament.admin.resources.chart-of-account-items.index');
    }

    public function render()
    {
        return view('livewire.c-o-a-template-items.create-c-o-a-items');
    }
}
