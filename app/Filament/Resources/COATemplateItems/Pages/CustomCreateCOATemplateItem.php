<?php

namespace App\Filament\Resources\COATemplateItems\Pages;

use App\Filament\Resources\COATemplateItems\COATemplateItemResource;
use Filament\Resources\Pages\Page;

class CustomCreateCOATemplateItem extends Page
{
    protected static string $resource = COATemplateItemResource::class;

    protected string $view = 'filament.resources.coa-template-items.pages.custom-create';

    public function getTitle(): string
    {
        return 'Create Chart of Account Items';
    }
}
