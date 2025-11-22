<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AccountSubtype;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountSubtypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AccountSubtype');
    }

    public function view(AuthUser $authUser, AccountSubtype $accountSubtype): bool
    {
        return $authUser->can('View:AccountSubtype');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AccountSubtype');
    }

    public function update(AuthUser $authUser, AccountSubtype $accountSubtype): bool
    {
        return $authUser->can('Update:AccountSubtype');
    }

    public function delete(AuthUser $authUser, AccountSubtype $accountSubtype): bool
    {
        return $authUser->can('Delete:AccountSubtype');
    }

    public function restore(AuthUser $authUser, AccountSubtype $accountSubtype): bool
    {
        return $authUser->can('Restore:AccountSubtype');
    }

    public function forceDelete(AuthUser $authUser, AccountSubtype $accountSubtype): bool
    {
        return $authUser->can('ForceDelete:AccountSubtype');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AccountSubtype');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AccountSubtype');
    }

    public function replicate(AuthUser $authUser, AccountSubtype $accountSubtype): bool
    {
        return $authUser->can('Replicate:AccountSubtype');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AccountSubtype');
    }

}