<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AccountClass;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountClassPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AccountClass');
    }

    public function view(AuthUser $authUser, AccountClass $accountClass): bool
    {
        return $authUser->can('View:AccountClass');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AccountClass');
    }

    public function update(AuthUser $authUser, AccountClass $accountClass): bool
    {
        return $authUser->can('Update:AccountClass');
    }

    public function delete(AuthUser $authUser, AccountClass $accountClass): bool
    {
        return $authUser->can('Delete:AccountClass');
    }

    public function restore(AuthUser $authUser, AccountClass $accountClass): bool
    {
        return $authUser->can('Restore:AccountClass');
    }

    public function forceDelete(AuthUser $authUser, AccountClass $accountClass): bool
    {
        return $authUser->can('ForceDelete:AccountClass');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AccountClass');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AccountClass');
    }

    public function replicate(AuthUser $authUser, AccountClass $accountClass): bool
    {
        return $authUser->can('Replicate:AccountClass');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AccountClass');
    }

}