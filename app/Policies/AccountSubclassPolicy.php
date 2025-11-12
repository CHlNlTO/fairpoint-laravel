<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AccountSubclass;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountSubclassPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AccountSubclass');
    }

    public function view(AuthUser $authUser, AccountSubclass $accountSubclass): bool
    {
        return $authUser->can('View:AccountSubclass');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AccountSubclass');
    }

    public function update(AuthUser $authUser, AccountSubclass $accountSubclass): bool
    {
        return $authUser->can('Update:AccountSubclass');
    }

    public function delete(AuthUser $authUser, AccountSubclass $accountSubclass): bool
    {
        return $authUser->can('Delete:AccountSubclass');
    }

    public function restore(AuthUser $authUser, AccountSubclass $accountSubclass): bool
    {
        return $authUser->can('Restore:AccountSubclass');
    }

    public function forceDelete(AuthUser $authUser, AccountSubclass $accountSubclass): bool
    {
        return $authUser->can('ForceDelete:AccountSubclass');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AccountSubclass');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AccountSubclass');
    }

    public function replicate(AuthUser $authUser, AccountSubclass $accountSubclass): bool
    {
        return $authUser->can('Replicate:AccountSubclass');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AccountSubclass');
    }

}