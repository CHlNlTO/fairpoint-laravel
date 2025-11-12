<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BusinessType;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusinessTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BusinessType');
    }

    public function view(AuthUser $authUser, BusinessType $businessType): bool
    {
        return $authUser->can('View:BusinessType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BusinessType');
    }

    public function update(AuthUser $authUser, BusinessType $businessType): bool
    {
        return $authUser->can('Update:BusinessType');
    }

    public function delete(AuthUser $authUser, BusinessType $businessType): bool
    {
        return $authUser->can('Delete:BusinessType');
    }

    public function restore(AuthUser $authUser, BusinessType $businessType): bool
    {
        return $authUser->can('Restore:BusinessType');
    }

    public function forceDelete(AuthUser $authUser, BusinessType $businessType): bool
    {
        return $authUser->can('ForceDelete:BusinessType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BusinessType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BusinessType');
    }

    public function replicate(AuthUser $authUser, BusinessType $businessType): bool
    {
        return $authUser->can('Replicate:BusinessType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BusinessType');
    }

}