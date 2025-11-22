<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\FiscalYearPeriod;
use Illuminate\Auth\Access\HandlesAuthorization;

class FiscalYearPeriodPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FiscalYearPeriod');
    }

    public function view(AuthUser $authUser, FiscalYearPeriod $fiscalYearPeriod): bool
    {
        return $authUser->can('View:FiscalYearPeriod');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FiscalYearPeriod');
    }

    public function update(AuthUser $authUser, FiscalYearPeriod $fiscalYearPeriod): bool
    {
        return $authUser->can('Update:FiscalYearPeriod');
    }

    public function delete(AuthUser $authUser, FiscalYearPeriod $fiscalYearPeriod): bool
    {
        return $authUser->can('Delete:FiscalYearPeriod');
    }

    public function restore(AuthUser $authUser, FiscalYearPeriod $fiscalYearPeriod): bool
    {
        return $authUser->can('Restore:FiscalYearPeriod');
    }

    public function forceDelete(AuthUser $authUser, FiscalYearPeriod $fiscalYearPeriod): bool
    {
        return $authUser->can('ForceDelete:FiscalYearPeriod');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FiscalYearPeriod');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FiscalYearPeriod');
    }

    public function replicate(AuthUser $authUser, FiscalYearPeriod $fiscalYearPeriod): bool
    {
        return $authUser->can('Replicate:FiscalYearPeriod');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FiscalYearPeriod');
    }

}