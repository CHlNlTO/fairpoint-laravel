<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GovernmentAgency;
use Illuminate\Auth\Access\HandlesAuthorization;

class GovernmentAgencyPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GovernmentAgency');
    }

    public function view(AuthUser $authUser, GovernmentAgency $governmentAgency): bool
    {
        return $authUser->can('View:GovernmentAgency');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GovernmentAgency');
    }

    public function update(AuthUser $authUser, GovernmentAgency $governmentAgency): bool
    {
        return $authUser->can('Update:GovernmentAgency');
    }

    public function delete(AuthUser $authUser, GovernmentAgency $governmentAgency): bool
    {
        return $authUser->can('Delete:GovernmentAgency');
    }

    public function restore(AuthUser $authUser, GovernmentAgency $governmentAgency): bool
    {
        return $authUser->can('Restore:GovernmentAgency');
    }

    public function forceDelete(AuthUser $authUser, GovernmentAgency $governmentAgency): bool
    {
        return $authUser->can('ForceDelete:GovernmentAgency');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GovernmentAgency');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GovernmentAgency');
    }

    public function replicate(AuthUser $authUser, GovernmentAgency $governmentAgency): bool
    {
        return $authUser->can('Replicate:GovernmentAgency');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GovernmentAgency');
    }

}