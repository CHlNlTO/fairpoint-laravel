<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BusinessRegistration;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusinessRegistrationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BusinessRegistration');
    }

    public function view(AuthUser $authUser, BusinessRegistration $businessRegistration): bool
    {
        return $authUser->can('View:BusinessRegistration');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BusinessRegistration');
    }

    public function update(AuthUser $authUser, BusinessRegistration $businessRegistration): bool
    {
        return $authUser->can('Update:BusinessRegistration');
    }

    public function delete(AuthUser $authUser, BusinessRegistration $businessRegistration): bool
    {
        return $authUser->can('Delete:BusinessRegistration');
    }

    public function restore(AuthUser $authUser, BusinessRegistration $businessRegistration): bool
    {
        return $authUser->can('Restore:BusinessRegistration');
    }

    public function forceDelete(AuthUser $authUser, BusinessRegistration $businessRegistration): bool
    {
        return $authUser->can('ForceDelete:BusinessRegistration');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BusinessRegistration');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BusinessRegistration');
    }

    public function replicate(AuthUser $authUser, BusinessRegistration $businessRegistration): bool
    {
        return $authUser->can('Replicate:BusinessRegistration');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BusinessRegistration');
    }

}