<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\IndustryType;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndustryTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IndustryType');
    }

    public function view(AuthUser $authUser, IndustryType $industryType): bool
    {
        return $authUser->can('View:IndustryType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IndustryType');
    }

    public function update(AuthUser $authUser, IndustryType $industryType): bool
    {
        return $authUser->can('Update:IndustryType');
    }

    public function delete(AuthUser $authUser, IndustryType $industryType): bool
    {
        return $authUser->can('Delete:IndustryType');
    }

    public function restore(AuthUser $authUser, IndustryType $industryType): bool
    {
        return $authUser->can('Restore:IndustryType');
    }

    public function forceDelete(AuthUser $authUser, IndustryType $industryType): bool
    {
        return $authUser->can('ForceDelete:IndustryType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:IndustryType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:IndustryType');
    }

    public function replicate(AuthUser $authUser, IndustryType $industryType): bool
    {
        return $authUser->can('Replicate:IndustryType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:IndustryType');
    }

}