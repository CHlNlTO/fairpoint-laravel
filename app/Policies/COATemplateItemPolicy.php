<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\COATemplateItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class COATemplateItemPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:COATemplateItem');
    }

    public function view(AuthUser $authUser, COATemplateItem $cOATemplateItem): bool
    {
        return $authUser->can('View:COATemplateItem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:COATemplateItem');
    }

    public function update(AuthUser $authUser, COATemplateItem $cOATemplateItem): bool
    {
        return $authUser->can('Update:COATemplateItem');
    }

    public function delete(AuthUser $authUser, COATemplateItem $cOATemplateItem): bool
    {
        return $authUser->can('Delete:COATemplateItem');
    }

    public function restore(AuthUser $authUser, COATemplateItem $cOATemplateItem): bool
    {
        return $authUser->can('Restore:COATemplateItem');
    }

    public function forceDelete(AuthUser $authUser, COATemplateItem $cOATemplateItem): bool
    {
        return $authUser->can('ForceDelete:COATemplateItem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:COATemplateItem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:COATemplateItem');
    }

    public function replicate(AuthUser $authUser, COATemplateItem $cOATemplateItem): bool
    {
        return $authUser->can('Replicate:COATemplateItem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:COATemplateItem');
    }

}