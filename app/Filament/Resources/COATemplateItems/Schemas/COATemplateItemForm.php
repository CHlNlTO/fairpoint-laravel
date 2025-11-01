<?php

namespace App\Filament\Resources\COATemplateItems\Schemas;

use App\Models\AccountClass;
use App\Models\AccountSubclass;
use App\Models\AccountSubtype;
use App\Models\AccountType;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class COATemplateItemForm
{
    /**
     * Load all account hierarchy data as collections once for better performance.
     */
    private static function loadAccountHierarchy(): Collection
    {
        return cache()->rememberForever('coa_hierarchy_collections', function () {
            return collect([
                'classes' => AccountClass::where('is_active', true)->pluck('name', 'id'),
                'subclasses' => AccountSubclass::where('is_active', true)->get(),
                'types' => AccountType::where('is_active', true)->get(),
                'subtypes' => AccountSubtype::where('is_active', true)->get(),
            ]);
        });
    }

    public static function configure(Schema $schema): Schema
    {
        // Load the data ONCE here and store it in a local variable.
        $hierarchy = self::loadAccountHierarchy();

        return $schema
            ->components([
                Repeater::make('items')
                    ->table([
                        TableColumn::make('Account Code')->width('100px'),
                        TableColumn::make('Account Name')->markAsRequired(),
                        TableColumn::make('Account Class')->markAsRequired(),
                        TableColumn::make('Account Subclass')->markAsRequired(),
                        TableColumn::make('Account Type')->markAsRequired(),
                        TableColumn::make('Account Subtype')->markAsRequired(),
                        TableColumn::make('Normal Balance')->markAsRequired(),
                        TableColumn::make('Active'),
                        TableColumn::make('Default'),
                    ])
                    ->compact()
                    ->schema([
                        TextInput::make('account_code')->hiddenLabel()->disabled()->dehydrated()->maxLength(6),
                        TextInput::make('account_name')->hiddenLabel()->required()->maxLength(200),
                        Select::make('account_class_id')
                            ->hiddenLabel()
                            ->searchable()->required()->preload()
                            // Use the pre-loaded data directly from the local variable.
                            ->options($hierarchy['classes'])
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('account_subclass_id', null);
                                $set('account_type_id', null);
                                $set('account_subtype_id', null);
                                $set('account_code', null);
                            }),
                        Select::make('account_subclass_id')
                            ->hiddenLabel()
                            ->searchable()->required()->preload()
                            // ->disabled(fn (Get $get) => !$get('account_class_id'))
                            // The `use` keyword passes the $hierarchy variable into the closure's scope.
                            ->options(function (Get $get) use ($hierarchy): array {
                                $classId = $get('account_class_id');
                                if (!$classId) return [];

                                return $hierarchy['subclasses']
                                    ->where('account_class_id', $classId)
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('account_type_id', null);
                                $set('account_subtype_id', null);
                                $set('account_code', null);
                            }),
                        Select::make('account_type_id')
                            ->hiddenLabel()
                            ->searchable()->required()->preload()
                            // ->disabled(fn (Get $get) => !$get('account_subclass_id'))
                            ->options(function (Get $get) use ($hierarchy): array {
                                $subclassId = $get('account_subclass_id');
                                if (!$subclassId) return [];

                                return $hierarchy['types']
                                    ->where('account_subclass_id', $subclassId)
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('account_subtype_id', null);
                                $set('account_code', null);
                            }),
                        Select::make('account_subtype_id')
                            ->hiddenLabel()
                            ->searchable()->required()->preload()
                            // ->disabled(fn (Get $get) => !$get('account_type_id'))
                            ->options(function (Get $get) use ($hierarchy): array {
                                $typeId = $get('account_type_id');
                                if (!$typeId) return [];

                                return $hierarchy['subtypes']
                                    ->where('account_type_id', $typeId)
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if (!$state) {
                                    $set('account_code', null);
                                    return;
                                }
                                $subtype = AccountSubtype::with(['accountType.accountSubclass.accountClass'])->find($state);
                                if (!$subtype) return;

                                $formItems = $get('../../items');
                                $code = self::generateAccountCode($subtype, $formItems);
                                $set('account_code', $code);
                            }),
                        Select::make('normal_balance')->hiddenLabel()->options(['debit' => 'Debit', 'credit' => 'Credit'])->required()->default('debit'),
                        Toggle::make('is_active')->hiddenLabel()->inline(false)->default(true),
                        Toggle::make('is_default')->hiddenLabel()->inline(false)->default(false),
                    ])
                    ->defaultItems(1)->addable(true)->deletable(true)->cloneable(true)->columnSpanFull(),
            ]);
    }

    private static function generateAccountCode(AccountSubtype $subtype, ?array $formItems = null): string
    {
        $classCode = str_pad((string)$subtype->accountType->accountSubclass->accountClass->code, 1, '0', STR_PAD_LEFT);
        $subclassCode = str_pad((string)$subtype->accountType->accountSubclass->code, 1, '0', STR_PAD_LEFT);
        $typeCode = str_pad((string)$subtype->accountType->code, 2, '0', STR_PAD_LEFT);

        $existingCodes = \App\Models\COATemplateItem::where('account_subtype_id', $subtype->id)->pluck('account_code')->toArray();

        if ($formItems && is_array($formItems)) {
            $formCodes = collect($formItems)->pluck('account_code')->filter()->toArray();
            $existingCodes = array_merge($existingCodes, $formCodes);
        }

        $highestCode = 0;
        foreach ($existingCodes as $code) {
            if (strlen($code) < 2) continue;
            $lastTwo = substr($code, -2);
            if (is_numeric($lastTwo) && (int)$lastTwo > $highestCode) {
                $highestCode = (int)$lastTwo;
            }
        }

        $nextSubtypeCode = str_pad($highestCode + 1, 2, '0', STR_PAD_LEFT);

        return $classCode . $subclassCode . $typeCode . $nextSubtypeCode;
    }
}
