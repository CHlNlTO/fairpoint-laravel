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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateCOAItems extends Component
{
    use WithFileUploads;

    public $items = [];
    public $csvFile;

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

        // // Initialize with 1 empty item
        // $this->items = [[
        //     'account_code' => '',
        //     'account_name' => '',
        //     'account_class_id' => '',
        //     'account_class_name' => '',
        //     'account_subclass_id' => '',
        //     'account_subclass_name' => '',
        //     'account_type_id' => '',
        //     'account_type_name' => '',
        //     'account_subtype_id' => '',
        //     'account_subtype_name' => '',
        //     'normal_balance' => 'debit',
        //     'is_active' => true,
        //     'is_default' => true,
        //     'business_type_ids' => [],
        //     'industry_type_ids' => [],
        //     'tax_type_ids' => [],
        // ]];
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
            // Try to load from database if not in cached data
            $subtypeModel = AccountSubtype::with(['accountType.accountSubclass.accountClass'])->find($subtypeId);
            if ($subtypeModel) {
                $subtype = [
                    'id' => $subtypeModel->id,
                    'account_type_id' => $subtypeModel->account_type_id,
                    'code' => $subtypeModel->code,
                    'name' => $subtypeModel->name,
                    'class_code' => $subtypeModel->accountType->accountSubclass->accountClass->code,
                    'subclass_code' => $subtypeModel->accountType->accountSubclass->code,
                    'type_code' => $subtypeModel->accountType->code,
                ];
                // Add to cached data
                $this->accountSubtypes[] = $subtype;
            } else {
            return;
            }
        }

        // Generate base code
        $classCode = str_pad((string)$subtype['class_code'], 1, '0', STR_PAD_LEFT);
        $subclassCode = str_pad((string)$subtype['subclass_code'], 1, '0', STR_PAD_LEFT);
        $typeCode = str_pad((string)$subtype['type_code'], 2, '0', STR_PAD_LEFT);

        // Collect used suffixes from database
        $usedSuffixes = [];

        $existingCodes = COATemplateItem::where('account_subtype_id', $subtypeId)
            ->pluck('account_code')
            ->toArray();

        foreach ($existingCodes as $code) {
            if (strlen($code) < 2) {
                continue;
            }

            $lastTwo = substr($code, -2);
            if (ctype_digit($lastTwo)) {
                $usedSuffixes[] = (int) $lastTwo;
            }
        }

        // Include codes from current form items (excluding the current index)
        foreach ($this->items as $itemIndex => $item) {
            if ($itemIndex === $index) {
                continue;
            }

            if (
                isset($item['account_code'], $item['account_subtype_id']) &&
                $item['account_code'] &&
                $item['account_subtype_id'] === $subtypeId
            ) {
                $suffix = substr($item['account_code'], -2);
                if (ctype_digit($suffix)) {
                    $usedSuffixes[] = (int) $suffix;
                }
            }
        }

        $usedSuffixes = array_unique($usedSuffixes);

        // Find the lowest available suffix starting from 0
        $nextNumber = 0;
        while (in_array($nextNumber, $usedSuffixes, true)) {
            $nextNumber++;
        }

        $nextSubtypeCode = str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

        $this->items[$index]['account_code'] = $classCode . $subclassCode . $typeCode . $nextSubtypeCode;
    }

    public function downloadTemplate()
    {
        $headers = [
            'Account Code',
            'Account Name',
            'Account Class',
            'Account Subclass',
            'Account Type',
            'Account Subtype',
            'Normal Balance',
            'Tax Type',
            'Industry Type',
            'Business Type'
        ];

        $filename = 'coa_template_' . date('Y-m-d') . '.csv';
        $file = fopen('php://temp', 'r+');

        // Write BOM for UTF-8
        fwrite($file, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($file, $headers);

        // Write example row
        fputcsv($file, [
            '110100',
            'Petty Cash Fund',
            'Assets',
            'Current Assets',
            'Cash and Cash Equivalents',
            'Petty Cash Fund',
            'Debit',
            'Any',
            'Gen',
            'Any'
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
            'Account Code',
            'Account Name',
            'Account Class',
            'Account Subclass',
            'Account Type',
            'Account Subtype',
            'Normal Balance',
            'Tax Type',
            'Industry Type',
            'Business Type'
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
                'account_code' => trim($row[0]) ?: '',
                'account_name' => trim($row[1]) ?: '',
                'account_class_name' => trim($row[2]) ?: '', // Store name for matching
                'account_subclass_name' => trim($row[3]) ?: '',
                'account_type_name' => trim($row[4]) ?: '',
                'account_subtype_name' => trim($row[5]) ?: '',
                'normal_balance' => strtolower(trim($row[6])) === 'credit' ? 'credit' : 'debit',
                'tax_type_name' => trim($row[7]) ?: '',
                'industry_type_name' => trim($row[8]) ?: '',
                'business_type_name' => trim($row[9]) ?: '',
                // Initialize IDs (will be set during matching)
                'account_class_id' => '',
                'account_subclass_id' => '',
                'account_type_id' => '',
                'account_subtype_id' => '',
                'is_active' => true,
                'is_default' => true,
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
                'account_code' => $item['account_code'],
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
                'account_code' => $this->items[0]['account_code'] ?? 'N/A',
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
                'account_code' => $parsedItems[0]['account_code'] ?? 'N/A',
                'account_name' => $parsedItems[0]['account_name'] ?? 'N/A',
                'account_class_id' => $parsedItems[0]['account_class_id'] ?? 'N/A',
                'account_subtype_id' => $parsedItems[0]['account_subtype_id'] ?? 'N/A',
            ] : 'No items',
        ]);

        // Generate account codes for items with matched subtypes but no code provided
        foreach ($this->items as $index => $item) {
            if ($item['account_subtype_id'] && empty($item['account_code'])) {
                Log::debug('Generating missing account code after CSV import', [
                    'item_index' => $index,
                    'account_subtype_id' => $item['account_subtype_id'],
                ]);
                $this->generateAccountCode($index);
            }
        }

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

    protected function getNextAccountClassCode()
    {
        $maxCode = AccountClass::max('code') ?? 0;
        return $maxCode + 1;
    }

    protected function getNextAccountSubclassCode($accountClassId)
    {
        $maxCode = AccountSubclass::where('account_class_id', $accountClassId)
            ->max('code') ?? 0;
        return $maxCode + 1;
    }

    protected function getNextAccountTypeCode($accountSubclassId)
    {
        $maxCode = AccountType::where('account_subclass_id', $accountSubclassId)
            ->max('code') ?? 0;
        return $maxCode + 1;
    }

    protected function getNextAccountSubtypeCode($accountTypeId)
    {
        $maxCode = AccountSubtype::where('account_type_id', $accountTypeId)
            ->max('code');

        if (is_null($maxCode)) {
            return 0;
        }

        return $maxCode + 1;
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
                'code' => $accountSubclass->code,
                'name' => $accountSubclass->name,
            ];
            return $accountSubclass->id;
        }

        // Create new
        $code = $this->getNextAccountSubclassCode($accountClassId);
        $accountSubclass = AccountSubclass::create([
            'account_class_id' => $accountClassId,
            'code' => $code,
            'name' => $name,
            'is_active' => true,
        ]);

        // Add to loaded data
        $this->accountSubclasses[] = [
            'id' => $accountSubclass->id,
            'account_class_id' => $accountSubclass->account_class_id,
            'code' => $accountSubclass->code,
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
                'code' => $accountType->code,
                'name' => $accountType->name,
            ];
            return $accountType->id;
        }

        // Create new
        $code = $this->getNextAccountTypeCode($accountSubclassId);
        $accountType = AccountType::create([
            'account_subclass_id' => $accountSubclassId,
            'code' => $code,
            'name' => $name,
            'is_active' => true,
            'is_system_defined' => true,
        ]);

        // Add to loaded data
        $this->accountTypes[] = [
            'id' => $accountType->id,
            'account_subclass_id' => $accountType->account_subclass_id,
            'code' => $accountType->code,
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
            // Reload with relationships to get codes
            $accountSubtype->load(['accountType.accountSubclass.accountClass']);
            // Add to loaded data
            $this->accountSubtypes[] = [
                'id' => $accountSubtype->id,
                'account_type_id' => $accountSubtype->account_type_id,
                'code' => $accountSubtype->code,
                'name' => $accountSubtype->name,
                'class_code' => $accountSubtype->accountType->accountSubclass->accountClass->code,
                'subclass_code' => $accountSubtype->accountType->accountSubclass->code,
                'type_code' => $accountSubtype->accountType->code,
            ];
            return $accountSubtype->id;
        }

        // Create new
        $code = $this->getNextAccountSubtypeCode($accountTypeId);
        $accountSubtype = AccountSubtype::create([
            'account_type_id' => $accountTypeId,
            'code' => $code,
            'name' => $name,
            'is_active' => true,
            'is_system_defined' => true,
        ]);

        // Load relationships and add to loaded data
        $accountSubtype->load(['accountType.accountSubclass.accountClass']);
        $this->accountSubtypes[] = [
            'id' => $accountSubtype->id,
            'account_type_id' => $accountSubtype->account_type_id,
            'code' => $accountSubtype->code,
            'name' => $accountSubtype->name,
            'class_code' => $accountSubtype->accountType->accountSubclass->accountClass->code,
            'subclass_code' => $accountSubtype->accountType->accountSubclass->code,
            'type_code' => $accountSubtype->accountType->code,
        ];

        return $accountSubtype->id;
    }

    protected function ensureUniqueAccountCodes(): void
    {
        // Collect all used codes from database (across ALL subtypes to avoid conflicts)
        $allExistingCodes = COATemplateItem::pluck('account_code')->toArray();
        // Flip array to use codes as keys for O(1) lookup
        $usedCodes = array_flip($allExistingCodes);

        Log::debug('ensureUniqueAccountCodes started', [
            'total_items' => count($this->items),
            'existing_codes_in_db' => count($allExistingCodes),
        ]);

        // First pass: Validate and keep codes that were provided in CSV (if they're valid and not in DB)
        foreach ($this->items as $index => $item) {
            if (!empty($item['account_subtype_id']) && !empty($item['account_code'])) {
                $providedCode = $item['account_code'];

                // Check if this code is already in database
                if (isset($usedCodes[$providedCode])) {
                    Log::warning('Code from CSV already exists in database, will regenerate', [
                        'index' => $index,
                        'account_name' => $item['account_name'],
                        'provided_code' => $providedCode,
                    ]);
                    // Clear it so it gets regenerated
                    $this->items[$index]['account_code'] = '';
                } else {
                    // Valid code, keep it and mark as used
                    $usedCodes[$providedCode] = true;
                    Log::debug('Keeping code from CSV', [
                        'index' => $index,
                        'account_name' => $item['account_name'],
                        'code' => $providedCode,
                    ]);
                }
            }
        }

        // Second pass: Generate codes for items that don't have one
        foreach ($this->items as $index => $item) {
            // Skip if already has a valid code
            if (!empty($item['account_code'])) {
                continue;
            }

            if (empty($item['account_subtype_id'])) {
                continue;
            }

            // Get subtype information
            $subtype = collect($this->accountSubtypes)->firstWhere('id', $item['account_subtype_id']);

            if (!$subtype) {
                $subtypeModel = AccountSubtype::with(['accountType.accountSubclass.accountClass'])->find($item['account_subtype_id']);

                if (!$subtypeModel) {
                    continue;
                }

                $subtype = [
                    'id' => $subtypeModel->id,
                    'account_type_id' => $subtypeModel->account_type_id,
                    'code' => $subtypeModel->code,
                    'name' => $subtypeModel->name,
                    'class_code' => $subtypeModel->accountType->accountSubclass->accountClass->code,
                    'subclass_code' => $subtypeModel->accountType->accountSubclass->code,
                    'type_code' => $subtypeModel->accountType->code,
                ];

                $this->accountSubtypes[] = $subtype;
            }

            // Build base code from hierarchy
            $classCode = str_pad((string) $subtype['class_code'], 1, '0', STR_PAD_LEFT);
            $subclassCode = str_pad((string) $subtype['subclass_code'], 1, '0', STR_PAD_LEFT);
            $typeCode = str_pad((string) $subtype['type_code'], 2, '0', STR_PAD_LEFT);
            $baseCode = $classCode . $subclassCode . $typeCode;

            // Find next available suffix for this base code
            $suffix = 0;
            while (isset($usedCodes[$baseCode . str_pad($suffix, 2, '0', STR_PAD_LEFT)])) {
                $suffix++;
                if ($suffix > 99) {
                    Log::error('Ran out of suffixes for base code', ['base_code' => $baseCode]);
                    break;
                }
            }

            $generatedCode = $baseCode . str_pad($suffix, 2, '0', STR_PAD_LEFT);
            $this->items[$index]['account_code'] = $generatedCode;
            $usedCodes[$generatedCode] = true;

            Log::debug('Generated new code', [
                'index' => $index,
                'account_name' => $item['account_name'],
                'generated_code' => $generatedCode,
            ]);
        }
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

            // Generate account code for items that still don't have subtype_id
            if (empty($this->items[$index]['account_subtype_id']) && !empty($item['account_subtype_id'])) {
                $this->items[$index]['account_subtype_id'] = $item['account_subtype_id'];
            }
        }

        // Ensure all items have account codes and they are unique for each account subtype
        // This will regenerate all codes to ensure uniqueness and proper sequencing
        $this->ensureUniqueAccountCodes();
        Log::debug('Items after ensureUniqueAccountCodes', [
            'first_item' => $this->items[0] ?? null,
        ]);

        // Check for duplicate codes within the current batch
        $batchCodes = [];
        $duplicatesFound = [];
        foreach ($this->items as $index => $item) {
            $code = $item['account_code'] ?? '';
            if ($code) {
                if (isset($batchCodes[$code])) {
                    $duplicatesFound[] = [
                        'code' => $code,
                        'first_index' => $batchCodes[$code],
                        'first_item' => $this->items[$batchCodes[$code]]['account_name'],
                        'duplicate_index' => $index,
                        'duplicate_item' => $item['account_name'],
                    ];
                }
                $batchCodes[$code] = $index;
            }
        }

        if (!empty($duplicatesFound)) {
            Log::error('Duplicate codes found in batch', ['duplicates' => $duplicatesFound]);
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
            $rules["items.{$index}.account_code"] = [
                'required',
                'size:6',
                function ($attribute, $value, $fail) {
                    // Check if account code already exists in database
                    // (This is a final safety check; ensureUniqueAccountCodes should prevent this)
                    $exists = COATemplateItem::where('account_code', $value)->exists();
                    if ($exists) {
                        $fail("The account code {$value} already exists in the database.");
                    }
                },
            ];
            $rules["items.{$index}.normal_balance"] = 'required|in:debit,credit';

            // Human-readable attribute names for validation errors
            $attributeNames["items.{$index}.account_name"] = "Account Name (row {$rowNumber})";
            $attributeNames["items.{$index}.account_class_id"] = "Account Class (row {$rowNumber})";
            $attributeNames["items.{$index}.account_subclass_id"] = "Account Subclass (row {$rowNumber})";
            $attributeNames["items.{$index}.account_type_id"] = "Account Type (row {$rowNumber})";
            $attributeNames["items.{$index}.account_subtype_id"] = "Account Subtype (row {$rowNumber})";
            $attributeNames["items.{$index}.account_code"] = "Account Code (row {$rowNumber})";
            $attributeNames["items.{$index}.normal_balance"] = "Normal Balance (row {$rowNumber})";
        }

        $this->validate($rules, [], $attributeNames);

        // Log all items before database creation
        Log::info('About to create COA items in database', [
            'total_items' => count($this->items),
            'items_summary' => array_map(function($item, $index) {
                return [
                    'index' => $index,
                    'account_code' => $item['account_code'] ?? 'N/A',
                    'account_name' => $item['account_name'] ?? 'N/A',
                    'account_subtype_id' => $item['account_subtype_id'] ?? 'N/A',
                ];
            }, $this->items, array_keys($this->items)),
        ]);

        // Create all items
        foreach ($this->items as $index => $item) {
            Log::debug('Creating COA item', [
                'index' => $index,
                'account_code' => $item['account_code'],
                'account_name' => $item['account_name'],
                'account_subtype_id' => $item['account_subtype_id'],
            ]);
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
