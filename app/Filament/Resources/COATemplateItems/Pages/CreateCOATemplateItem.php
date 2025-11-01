<?php

namespace App\Filament\Resources\COATemplateItems\Pages;

use App\Filament\Resources\COATemplateItems\COATemplateItemResource;
use App\Models\COAItemBusinessType;
use App\Models\COAItemIndustryType;
use App\Models\COAItemTaxType;
use App\Models\COATemplateItem;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCOATemplateItem extends CreateRecord
{
    protected static string $resource = COATemplateItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Let the original data pass through to handleRecordCreation
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Handle bulk creation with multiple items
        if (isset($data['items']) && is_array($data['items'])) {
            $createdItems = [];

            // First pass: ensure all account codes are generated and unique within the form
            $generatedCodes = [];

            foreach ($data['items'] as &$item) {
                // If account code is not set or empty, generate it
                if (empty($item['account_code']) && !empty($item['account_subtype_id'])) {
                    $subtype = \App\Models\AccountSubtype::with(['accountType.accountSubclass.accountClass'])->find($item['account_subtype_id']);
                    if ($subtype) {
                        $item['account_code'] = $this->generateAccountCode($subtype, $generatedCodes);
                        $generatedCodes[] = $item['account_code'];
                    }
                } elseif (!empty($item['account_code'])) {
                    // Check for duplicates and regenerate if needed
                    $desiredCode = $item['account_code'];
                    while (in_array($desiredCode, $generatedCodes)) {
                        // Duplicate found, increment the last two digits
                        $prefix = substr($desiredCode, 0, 4);
                        $lastTwo = (int)substr($desiredCode, -2) + 1;
                        $desiredCode = $prefix . str_pad($lastTwo, 2, '0', STR_PAD_LEFT);
                    }
                    $item['account_code'] = $desiredCode;
                    $generatedCodes[] = $desiredCode;
                }
            }

            unset($item); // Break the reference

            foreach ($data['items'] as $item) {
                // Extract pivot data before creating the main record
                $businessTypeIds = $item['business_type_ids'] ?? [];
                $industryTypeIds = $item['industry_type_ids'] ?? [];
                $taxTypeIds = $item['tax_type_ids'] ?? [];

                // Create the main COATemplateItem
                $coaItem = COATemplateItem::create([
                    'account_code' => $item['account_code'],
                    'account_name' => $item['account_name'],
                    'account_subtype_id' => $item['account_subtype_id'],
                    'normal_balance' => $item['normal_balance'],
                    'is_active' => $item['is_active'] ?? true,
                    'is_default' => $item['is_default'] ?? false,
                ]);

                // Create pivot records for business types
                foreach ($businessTypeIds as $businessTypeId) {
                    COAItemBusinessType::create([
                        'account_item_id' => $coaItem->id,
                        'business_type_id' => $businessTypeId,
                    ]);
                }

                // Create pivot records for industry types
                foreach ($industryTypeIds as $industryTypeId) {
                    COAItemIndustryType::create([
                        'account_item_id' => $coaItem->id,
                        'industry_type_id' => $industryTypeId,
                    ]);
                }

                // Create pivot records for tax types
                foreach ($taxTypeIds as $taxTypeId) {
                    COAItemTaxType::create([
                        'account_item_id' => $coaItem->id,
                        'tax_type_id' => $taxTypeId,
                    ]);
                }

                $createdItems[] = $coaItem;
            }

            // Return the first created item for redirect purposes
            return $createdItems[0];
        }

        // Fallback to default creation (single record)
        return COATemplateItem::create($data);
    }

    /**
     * Generate the next available account code for a given subtype
     */
    private function generateAccountCode(\App\Models\AccountSubtype $subtype, ?array $formItems = null): string
    {
        // Convert integer codes to strings for concatenation
        $classCode = str_pad((string)$subtype->accountType->accountSubclass->accountClass->code, 1, '0', STR_PAD_LEFT);
        $subclassCode = str_pad((string)$subtype->accountType->accountSubclass->code, 1, '0', STR_PAD_LEFT);
        $typeCode = str_pad((string)$subtype->accountType->code, 2, '0', STR_PAD_LEFT);

        // Get the highest existing code for this subtype from database
        $existingCodes = COATemplateItem::where('account_subtype_id', $subtype->id)
            ->pluck('account_code')
            ->toArray();

        // Also check for codes in the current form items
        if ($formItems && is_array($formItems)) {
            $formCodes = collect($formItems)
                ->pluck('account_code')
                ->filter()
                ->toArray();
            $existingCodes = array_merge($existingCodes, $formCodes);
        }

        $highestCode = 0;
        foreach ($existingCodes as $code) {
            $lastTwo = substr($code, -2);
            if (is_numeric($lastTwo) && (int)$lastTwo > $highestCode) {
                $highestCode = (int)$lastTwo;
            }
        }

        $nextSubtypeCode = str_pad($highestCode + 1, 2, '0', STR_PAD_LEFT);

        return $classCode . $subclassCode . $typeCode . $nextSubtypeCode;
    }
}
