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
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateCOAItems extends Component
{
    use WithFileUploads;

    public $items = [];
    public $csvFile;
    public string $mode = 'create';
    public array $ignoreCoaItemIds = [];

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
            ->orderBy('name')
            ->get()
            ->map(function ($subclass) {
                return [
                    'id' => $subclass->id,
                    'account_class_id' => $subclass->account_class_id,
                    'name' => $subclass->name,
                ];
            })
            ->toArray();

        $this->accountTypes = AccountType::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'account_subclass_id' => $type->account_subclass_id,
                    'name' => $type->name,
                ];
            })
            ->toArray();

        $this->accountSubtypes = AccountSubtype::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($subtype) {
                return [
                    'id' => $subtype->id,
                    'account_type_id' => $subtype->account_type_id,
                    'name' => $subtype->name,
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


    public function downloadTemplate()
    {
        $headers = [
            'Account Name',
            'Account Class',
            'Account Subclass',
            'Account Type',
            'Account Subtype',
            'Normal Balance',
            'Tax Type',
            'Industry Type',
            'Business Type',
            'Is Default'
        ];

        $filename = 'coa_template_' . date('Y-m-d') . '.csv';
        $file = fopen('php://temp', 'r+');

        // Write BOM for UTF-8
        fwrite($file, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($file, $headers);

        // Write example row
        fputcsv($file, [
            'Petty Cash Fund',
            'Assets',
            'Current Assets',
            'Cash and Cash Equivalents',
            'Petty Cash Fund',
            'Debit',
            'Any',
            'Gen',
            'Any',
            'Yes'
        ]);

        rewind($file);
        $csv = stream_get_contents($file);
        fclose($file);

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function processCsv()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');

        // Skip BOM if present
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        // Read and validate header
        $header = fgetcsv($file);
        $expectedHeaders = [
            'Account Name',
            'Account Class',
            'Account Subclass',
            'Account Type',
            'Account Subtype',
            'Normal Balance',
            'Tax Type',
            'Industry Type',
            'Business Type',
            'Is Default'
        ];

        if ($header !== $expectedHeaders) {
            fclose($file);
            $this->addError('csvFile', 'Invalid CSV format. Please download the template and use it.');
            return;
        }

        $parsedItems = [];
        $rowNumber = 1;

        while (($row = fgetcsv($file)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            if (count($row) !== 10) {
                continue; // Skip invalid rows
            }

            // Map CSV row to item structure
            $item = [
                'account_name' => trim($row[0]) ?: '',
                'account_class_name' => trim($row[1]) ?: '', // Store name for matching
                'account_subclass_name' => trim($row[2]) ?: '',
                'account_type_name' => trim($row[3]) ?: '',
                'account_subtype_name' => trim($row[4]) ?: '',
                'normal_balance' => strtolower(trim($row[5])) === 'credit' ? 'credit' : 'debit',
                'tax_type_name' => trim($row[6]) ?: '',
                'industry_type_name' => trim($row[7]) ?: '',
                'business_type_name' => trim($row[8]) ?: '',
                // Initialize IDs (will be set during matching)
                'account_class_id' => '',
                'account_subclass_id' => '',
                'account_type_id' => '',
                'account_subtype_id' => '',
                'is_active' => true,
                'is_default' => strtolower(trim($row[9])) === 'yes',
                'business_type_ids' => [],
                'industry_type_ids' => [],
                'tax_type_ids' => [],
                // Flags to track if record needs to be created
                'needs_class_creation' => false,
                'needs_subclass_creation' => false,
                'needs_type_creation' => false,
                'needs_subtype_creation' => false,
            ];

            // Match existing records
            $this->matchHierarchyRecords($item);

            // Match type records (Tax, Industry, Business)
            $this->matchTypeRecords($item);

            Log::debug('CSV row processed', [
                'row_number' => $rowNumber,
                'account_name' => $item['account_name'],
                'account_class_name' => $item['account_class_name'],
                'account_class_id' => $item['account_class_id'],
                'account_subclass_name' => $item['account_subclass_name'],
                'account_subclass_id' => $item['account_subclass_id'],
                'account_type_name' => $item['account_type_name'],
                'account_type_id' => $item['account_type_id'],
                'account_subtype_name' => $item['account_subtype_name'],
                'account_subtype_id' => $item['account_subtype_id'],
            ]);

            $parsedItems[] = $item;
        }

        fclose($file);

        // Log existing items before clearing (for debugging)
        Log::info('Items before CSV import', [
            'existing_items_count' => count($this->items),
            'first_item' => !empty($this->items[0]) ? [
                'account_name' => $this->items[0]['account_name'] ?? 'N/A',
                'account_class_id' => $this->items[0]['account_class_id'] ?? 'N/A',
            ] : 'No items',
        ]);

        // CLEAR ALL existing items first (clean slate)
        $this->items = [];

        // Replace with parsed items from CSV
        $this->items = $parsedItems;

        Log::info('CSV import completed', [
            'total_rows' => count($parsedItems),
            'first_item_after_import' => !empty($parsedItems[0]) ? [
                'account_name' => $parsedItems[0]['account_name'] ?? 'N/A',
                'account_class_id' => $parsedItems[0]['account_class_id'] ?? 'N/A',
                'account_subtype_id' => $parsedItems[0]['account_subtype_id'] ?? 'N/A',
            ] : 'No items',
        ]);

        session()->flash('success', count($parsedItems) . ' items imported from CSV successfully.');
        $this->csvFile = null; // Clear the file input
    }

    protected function matchHierarchyRecords(&$item)
    {
        // Match Account Class
        if ($item['account_class_name']) {
            $matched = collect($this->accountClasses)->firstWhere('name', $item['account_class_name']);
            if ($matched) {
                $item['account_class_id'] = $matched['id'];
            } else {
                $item['needs_class_creation'] = true;
            }
        }

        // Match Account Subclass (only if class is matched or will be created)
        if ($item['account_subclass_name'] && ($item['account_class_id'] || ($item['needs_class_creation'] ?? false))) {
            $classId = $item['account_class_id'];
            $matched = collect($this->accountSubclasses)->first(function ($subclass) use ($item, $classId) {
                return $subclass['name'] === $item['account_subclass_name'] &&
                       ($classId ? $subclass['account_class_id'] === $classId : true);
            });

            if ($matched) {
                $item['account_subclass_id'] = $matched['id'];
            } else {
                $item['needs_subclass_creation'] = true;
            }
        }

        // Match Account Type
        if ($item['account_type_name'] && ($item['account_subclass_id'] || ($item['needs_subclass_creation'] ?? false))) {
            $subclassId = $item['account_subclass_id'];
            $matched = collect($this->accountTypes)->first(function ($type) use ($item, $subclassId) {
                return $type['name'] === $item['account_type_name'] &&
                       ($subclassId ? $type['account_subclass_id'] === $subclassId : true);
            });

            if ($matched) {
                $item['account_type_id'] = $matched['id'];
            } else {
                $item['needs_type_creation'] = true;
            }
        }

        // Match Account Subtype
        if ($item['account_subtype_name'] && ($item['account_type_id'] || ($item['needs_type_creation'] ?? false))) {
            $typeId = $item['account_type_id'];
            $matched = collect($this->accountSubtypes)->first(function ($subtype) use ($item, $typeId) {
                return $subtype['name'] === $item['account_subtype_name'] &&
                       ($typeId ? $subtype['account_type_id'] === $typeId : true);
            });

            if ($matched) {
                $item['account_subtype_id'] = $matched['id'];
            } else {
                $item['needs_subtype_creation'] = true;
            }
        }
    }

    protected function matchTypeRecords(&$item)
    {
        // Handle Tax Type, Industry Type, Business Type
        // They are mutually exclusive - only one category can have selections

        $taxTypeName = trim($item['tax_type_name'] ?? '');
        $industryTypeName = trim($item['industry_type_name'] ?? '');
        $businessTypeName = trim($item['business_type_name'] ?? '');

        // Handle "Any" special value - skip matching
        if (strtolower($taxTypeName) === 'any') $taxTypeName = '';
        if (strtolower($industryTypeName) === 'any') $industryTypeName = '';
        if (strtolower($businessTypeName) === 'any') $businessTypeName = '';

        // Determine which category has data (take first non-empty)
        if ($taxTypeName) {
            // Parse comma-separated values
            $taxTypeNames = array_map('trim', explode(',', $taxTypeName));
            $matchedIds = [];
            foreach ($taxTypeNames as $name) {
                $matched = collect($this->taxTypes)->firstWhere('name', $name);
                if ($matched) {
                    $matchedIds[] = $matched['id'];
                }
            }
            if (!empty($matchedIds)) {
                $item['tax_type_ids'] = $matchedIds;
            }
        } elseif ($industryTypeName) {
            // Parse comma-separated values
            $industryTypeNames = array_map('trim', explode(',', $industryTypeName));
            $matchedIds = [];
            foreach ($industryTypeNames as $name) {
                $matched = collect($this->industryTypes)->firstWhere('name', $name);
                if ($matched) {
                    $matchedIds[] = $matched['id'];
                }
            }
            if (!empty($matchedIds)) {
                $item['industry_type_ids'] = $matchedIds;
            }
        } elseif ($businessTypeName) {
            // Parse comma-separated values
            $businessTypeNames = array_map('trim', explode(',', $businessTypeName));
            $matchedIds = [];
            foreach ($businessTypeNames as $name) {
                $matched = collect($this->businessTypes)->firstWhere('name', $name);
                if ($matched) {
                    $matchedIds[] = $matched['id'];
                }
            }
            if (!empty($matchedIds)) {
                $item['business_type_ids'] = $matchedIds;
            }
        }
    }


    protected function createOrGetAccountClass($name, $normalBalance)
    {
        // Check if already exists in loaded data
        $existing = collect($this->accountClasses)->firstWhere('name', $name);
        if ($existing) {
            return $existing['id'];
        }

        // Check database
        $accountClass = AccountClass::where('name', $name)->first();
        if ($accountClass) {
            // Add to loaded data
            $this->accountClasses[] = [
                'id' => $accountClass->id,
                'code' => $accountClass->code,
                'name' => $accountClass->name,
            ];
            return $accountClass->id;
        }

        // Create new
        $code = $this->getNextAccountClassCode();
        $accountClass = AccountClass::create([
            'code' => $code,
            'name' => $name,
            'normal_balance' => $normalBalance,
            'is_active' => true,
        ]);

        // Add to loaded data
        $this->accountClasses[] = [
            'id' => $accountClass->id,
            'code' => $accountClass->code,
            'name' => $accountClass->name,
        ];

        return $accountClass->id;
    }

    protected function createOrGetAccountSubclass($name, $accountClassId)
    {
        // Check if already exists in loaded data
        $existing = collect($this->accountSubclasses)->first(function ($subclass) use ($name, $accountClassId) {
            return $subclass['name'] === $name && $subclass['account_class_id'] === $accountClassId;
        });
        if ($existing) {
            return $existing['id'];
        }

        // Check database
        $accountSubclass = AccountSubclass::where('name', $name)
            ->where('account_class_id', $accountClassId)
            ->first();
        if ($accountSubclass) {
            // Add to loaded data
            $this->accountSubclasses[] = [
                'id' => $accountSubclass->id,
                'account_class_id' => $accountSubclass->account_class_id,
                'name' => $accountSubclass->name,
            ];
            return $accountSubclass->id;
        }

        // Create new
        $accountSubclass = AccountSubclass::create([
            'account_class_id' => $accountClassId,
            'name' => $name,
            'is_active' => true,
        ]);

        // Add to loaded data
        $this->accountSubclasses[] = [
            'id' => $accountSubclass->id,
            'account_class_id' => $accountSubclass->account_class_id,
            'name' => $accountSubclass->name,
        ];

        return $accountSubclass->id;
    }

    protected function createOrGetAccountType($name, $accountSubclassId)
    {
        // Check if already exists in loaded data
        $existing = collect($this->accountTypes)->first(function ($type) use ($name, $accountSubclassId) {
            return $type['name'] === $name && $type['account_subclass_id'] === $accountSubclassId;
        });
        if ($existing) {
            return $existing['id'];
        }

        // Check database
        $accountType = AccountType::where('name', $name)
            ->where('account_subclass_id', $accountSubclassId)
            ->first();
        if ($accountType) {
            // Add to loaded data
            $this->accountTypes[] = [
                'id' => $accountType->id,
                'account_subclass_id' => $accountType->account_subclass_id,
                'name' => $accountType->name,
            ];
            return $accountType->id;
        }

        // Create new
        $accountType = AccountType::create([
            'account_subclass_id' => $accountSubclassId,
            'name' => $name,
            'is_active' => true,
            'is_system_defined' => true,
        ]);

        // Add to loaded data
        $this->accountTypes[] = [
            'id' => $accountType->id,
            'account_subclass_id' => $accountType->account_subclass_id,
            'name' => $accountType->name,
        ];

        return $accountType->id;
    }

    protected function createOrGetAccountSubtype($name, $accountTypeId)
    {
        // Check if already exists in loaded data
        $existing = collect($this->accountSubtypes)->first(function ($subtype) use ($name, $accountTypeId) {
            return $subtype['name'] === $name && $subtype['account_type_id'] === $accountTypeId;
        });
        if ($existing) {
            return $existing['id'];
        }

        // Check database
        $accountSubtype = AccountSubtype::where('name', $name)
            ->where('account_type_id', $accountTypeId)
            ->first();
        if ($accountSubtype) {
            // Add to loaded data
            $this->accountSubtypes[] = [
                'id' => $accountSubtype->id,
                'account_type_id' => $accountSubtype->account_type_id,
                'name' => $accountSubtype->name,
            ];
            return $accountSubtype->id;
        }

        // Create new
        $accountSubtype = AccountSubtype::create([
            'account_type_id' => $accountTypeId,
            'name' => $name,
            'is_active' => true,
            'is_system_defined' => true,
        ]);

        // Add to loaded data
        $this->accountSubtypes[] = [
            'id' => $accountSubtype->id,
            'account_type_id' => $accountSubtype->account_type_id,
            'name' => $accountSubtype->name,
        ];

        return $accountSubtype->id;
    }


    public function save()
    {
        // First, create any missing hierarchy records and update items with IDs
        foreach ($this->items as $index => $item) {
            if ($index === 0) {
                Log::debug('Pre-save item snapshot', [
                    'item_index' => $index,
                    'item' => $item,
                ]);
            }

            // Create Account Class if needed
            if (
                !empty($item['account_class_name']) &&
                (empty($item['account_class_id']) || ($item['needs_class_creation'] ?? false))
            ) {
                $this->items[$index]['account_class_id'] = $this->createOrGetAccountClass(
                    $item['account_class_name'],
                    $item['normal_balance'] ?? 'debit'
                );
            }

            // Create Account Subclass if needed
            if (!empty($item['account_subclass_name']) &&
                $this->items[$index]['account_class_id'] &&
                (empty($item['account_subclass_id']) || ($item['needs_subclass_creation'] ?? false))) {
                $this->items[$index]['account_subclass_id'] = $this->createOrGetAccountSubclass(
                    $item['account_subclass_name'],
                    $this->items[$index]['account_class_id']
                );
            }

            // Create Account Type if needed
            if (!empty($item['account_type_name']) &&
                $this->items[$index]['account_subclass_id'] &&
                (empty($item['account_type_id']) || ($item['needs_type_creation'] ?? false))) {
                $this->items[$index]['account_type_id'] = $this->createOrGetAccountType(
                    $item['account_type_name'],
                    $this->items[$index]['account_subclass_id']
                );
            }

            // Create Account Subtype if needed
            if (!empty($item['account_subtype_name']) &&
                $this->items[$index]['account_type_id'] &&
                (empty($item['account_subtype_id']) || ($item['needs_subtype_creation'] ?? false))) {
                $subtypeId = $this->createOrGetAccountSubtype(
                    $item['account_subtype_name'],
                    $this->items[$index]['account_type_id']
                );
                $this->items[$index]['account_subtype_id'] = $subtypeId;
            }

            // Ensure subtype_id is set
            if (empty($this->items[$index]['account_subtype_id']) && !empty($item['account_subtype_id'])) {
                $this->items[$index]['account_subtype_id'] = $item['account_subtype_id'];
            }
        }

        // Validate all items
        $rules = [];
        $attributeNames = [];
        foreach ($this->items as $index => $item) {
            $rowNumber = $index + 1;

            $rules["items.{$index}.account_name"] = 'required|max:200';
            $rules["items.{$index}.account_class_id"] = 'required';
            $rules["items.{$index}.account_subclass_id"] = 'required';
            $rules["items.{$index}.account_type_id"] = 'required';
            $rules["items.{$index}.account_subtype_id"] = 'required';
            $rules["items.{$index}.normal_balance"] = 'required|in:debit,credit';

            // Human-readable attribute names for validation errors
            $attributeNames["items.{$index}.account_name"] = "Account Name (row {$rowNumber})";
            $attributeNames["items.{$index}.account_class_id"] = "Account Class (row {$rowNumber})";
            $attributeNames["items.{$index}.account_subclass_id"] = "Account Subclass (row {$rowNumber})";
            $attributeNames["items.{$index}.account_type_id"] = "Account Type (row {$rowNumber})";
            $attributeNames["items.{$index}.account_subtype_id"] = "Account Subtype (row {$rowNumber})";
            $attributeNames["items.{$index}.normal_balance"] = "Normal Balance (row {$rowNumber})";
        }

        $this->validate($rules, [], $attributeNames);

        // Log all items before database creation
        Log::info('About to create COA items in database', [
            'total_items' => count($this->items),
            'items_summary' => array_map(function($item, $index) {
                return [
                    'index' => $index,
                    'account_name' => $item['account_name'] ?? 'N/A',
                    'account_subtype_id' => $item['account_subtype_id'] ?? 'N/A',
                ];
            }, $this->items, array_keys($this->items)),
        ]);

        // Create all items
        foreach ($this->items as $index => $item) {
            Log::debug('Creating COA item', [
                'index' => $index,
                'account_name' => $item['account_name'],
                'account_subtype_id' => $item['account_subtype_id'],
            ]);
            $coaItem = COATemplateItem::create([
                'account_name' => $item['account_name'],
                'account_subtype_id' => $item['account_subtype_id'],
                'normal_balance' => $item['normal_balance'],
                'is_active' => $item['is_active'] ?? true,
                'is_default' => $item['is_default'] ?? false,
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
