<?php

namespace App\Filament\Resources\BusinessRegistrations\Pages;

use App\Filament\Resources\BusinessRegistrations\BusinessRegistrationResource;
use Filament\Resources\Pages\Page;

class CreateBusinessRegistration extends Page
{
    protected static string $resource = BusinessRegistrationResource::class;

    protected string $view = 'filament.resources.business-registrations.pages.create';

    public function getTitle(): string
    {
        return 'Register Business';
    }
}
