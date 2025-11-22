<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TaxCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaxCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TaxCategory');
    }

    public function view(AuthUser $authUser, TaxCategory $taxCategory): bool
    {
        return $authUser->can('View:TaxCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TaxCategory');
    }

    public function update(AuthUser $authUser, TaxCategory $taxCategory): bool
    {
        return $authUser->can('Update:TaxCategory');
    }

    public function delete(AuthUser $authUser, TaxCategory $taxCategory): bool
    {
        return $authUser->can('Delete:TaxCategory');
    }

    public function restore(AuthUser $authUser, TaxCategory $taxCategory): bool
    {
        return $authUser->can('Restore:TaxCategory');
    }

    public function forceDelete(AuthUser $authUser, TaxCategory $taxCategory): bool
    {
        return $authUser->can('ForceDelete:TaxCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TaxCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TaxCategory');
    }

    public function replicate(AuthUser $authUser, TaxCategory $taxCategory): bool
    {
        return $authUser->can('Replicate:TaxCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TaxCategory');
    }

}