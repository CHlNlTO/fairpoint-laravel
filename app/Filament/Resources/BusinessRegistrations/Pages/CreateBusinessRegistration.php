<?php

namespace App\Filament\Resources\BusinessRegistrations\Pages;

use App\Filament\Resources\BusinessRegistrations\BusinessRegistrationResource;
use App\Models\BusinessRegistration;
use App\Models\BusinessGovernmentRegistration;
use App\Models\GovernmentAgency;
use App\Models\TaxCategory;
use App\Models\COATemplateItem;
use App\Models\COAItemBusinessType;
use App\Models\COAItemTaxType;
use App\Models\COAItemIndustryType;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateBusinessRegistration extends CreateRecord
{
    protected static string $resource = BusinessRegistrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the current user as the owner
        $data['user_id'] = Auth::id();
        $data['is_active'] = true;

        // Clean and format postal code to ensure it's exactly 4 digits
        if (isset($data['postal_code']) && $data['postal_code'] !== null) {
            // Remove any whitespace and ensure it's a string
            $postalCode = trim((string) $data['postal_code']);
            // Remove any non-digit characters
            $postalCode = preg_replace('/[^0-9]/', '', $postalCode);
            // Ensure it's exactly 4 digits
            if (preg_match('/^\d{4}$/', $postalCode)) {
                $data['postal_code'] = $postalCode;
            } else {
                // If it doesn't match, set to null to avoid constraint violation
                $data['postal_code'] = null;
            }
        } else {
            // Explicitly set to null if not provided
            $data['postal_code'] = null;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): BusinessRegistration
    {
        return DB::transaction(function () use ($data) {
            // Extract relationships data
            $industryTypeIds = $data['industry_type_ids'] ?? [];
            $governmentAgencyIds = $data['government_agency_ids'] ?? [];
            // Note: coa_template_item_ids is no longer collected from form
            // It will be calculated automatically based on selections

            // Collect tax type IDs from all categories
            $taxTypeIds = [];
            $birAgency = GovernmentAgency::where('code', 'BIR')
                ->where('is_active', true)
                ->first();

            if ($birAgency && in_array($birAgency->id, $governmentAgencyIds)) {
                $categories = TaxCategory::query()
                    ->where('is_active', true)
                    ->get();

                foreach ($categories as $category) {
                    if (strtolower($category->name) === 'additional tax') {
                        $categoryTaxIds = $data["tax_type_ids_{$category->id}"] ?? [];
                        $taxTypeIds = array_merge($taxTypeIds, $categoryTaxIds);
                    } else {
                        $categoryTaxId = $data["tax_type_id_{$category->id}"] ?? null;
                        if ($categoryTaxId) {
                            $taxTypeIds[] = $categoryTaxId;
                        }
                    }
                }
            }

            // Remove relationship data from main data array
            unset(
                $data['industry_type_ids'],
                $data['government_agency_ids'],
                $data['coa_template_items_display'] // Remove the display-only repeater data
            );

            // Remove tax type fields
            $categories = TaxCategory::query()->where('is_active', true)->get();
            foreach ($categories as $category) {
                if (strtolower($category->name) === 'additional tax') {
                    unset($data["tax_type_ids_{$category->id}"]);
                } else {
                    unset($data["tax_type_id_{$category->id}"]);
                }
            }

            // Create the main business registration record
            $businessRegistration = BusinessRegistration::create($data);

            // Sync industry types
            if (!empty($industryTypeIds)) {
                $businessRegistration->industryTypes()->sync($industryTypeIds);
            }

            // Sync tax types
            if (!empty($taxTypeIds)) {
                $businessRegistration->taxTypes()->sync($taxTypeIds);
            }

            // Create government registrations
            foreach ($governmentAgencyIds as $agencyId) {
                BusinessGovernmentRegistration::create([
                    'business_registration_id' => $businessRegistration->id,
                    'government_agency_id' => $agencyId,
                    'is_active' => true,
                ]);
            }

            // Handle COA template items
            // Automatically determine COA items based on selections
            $coaItemIds = [];

            // Get default COA items
            $defaultCoaIds = COATemplateItem::query()
                ->where('is_default', true)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            $coaItemIds = array_merge($coaItemIds, $defaultCoaIds);

            // Get COA items by business type
            if ($businessRegistration->business_type_id) {
                $businessTypeCoaIds = COAItemBusinessType::query()
                    ->where('business_type_id', $businessRegistration->business_type_id)
                    ->pluck('account_item_id')
                    ->toArray();
                $coaItemIds = array_merge($coaItemIds, $businessTypeCoaIds);
            }

            // Get COA items by industry types
            if (!empty($industryTypeIds)) {
                $industryTypeCoaIds = COAItemIndustryType::query()
                    ->whereIn('industry_type_id', $industryTypeIds)
                    ->pluck('account_item_id')
                    ->toArray();
                $coaItemIds = array_merge($coaItemIds, $industryTypeCoaIds);
            }

            // Get COA items by tax types
            if (!empty($taxTypeIds)) {
                $taxTypeCoaIds = COAItemTaxType::query()
                    ->whereIn('tax_type_id', $taxTypeIds)
                    ->pluck('account_item_id')
                    ->toArray();
                $coaItemIds = array_merge($coaItemIds, $taxTypeCoaIds);
            }

            // Remove duplicates
            $coaItemIds = array_unique($coaItemIds);

            // Save COA items if you have a pivot table relationship
            // Example: $businessRegistration->coaTemplateItems()->sync($coaItemIds);
            // Or store in JSON column: $businessRegistration->update(['coa_template_item_ids' => $coaItemIds]);

            // Note: You'll need to create the relationship/pivot table or JSON column
            // For now, we're just collecting the IDs - you can implement the storage logic based on your needs

            return $businessRegistration;
        });
    }
}
