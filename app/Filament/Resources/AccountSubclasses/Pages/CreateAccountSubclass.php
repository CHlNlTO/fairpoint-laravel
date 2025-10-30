<?php

namespace App\Filament\Resources\AccountSubclasses\Pages;

use App\Filament\Resources\AccountSubclasses\AccountSubclassResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAccountSubclass extends CreateRecord
{
    protected static string $resource = AccountSubclassResource::class;
}
