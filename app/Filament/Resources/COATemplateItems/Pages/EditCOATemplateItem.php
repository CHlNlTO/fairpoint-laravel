<?php

namespace App\Filament\Resources\COATemplateItems\Pages;

use App\Filament\Resources\COATemplateItems\COATemplateItemResource;
use App\Models\COATemplateItem;
use Filament\Resources\Pages\Page;

class EditCOATemplateItem extends Page
{
    protected static string $resource = COATemplateItemResource::class;

    // protected static ?string $navigationIcon = null;

    protected string $view = 'filament.resources.coa-template-items.pages.edit';

    public COATemplateItem $record;

    public function mount(COATemplateItem $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string
    {
        return 'Edit Chart of Account Item';
    }
}
