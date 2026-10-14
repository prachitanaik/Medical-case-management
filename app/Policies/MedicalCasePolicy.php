<?php

namespace App\Policies;

use App\Models\MedicalCase;
use App\Models\User;

/**
 * Role rules (see README):
 *  - admin    : view, create, edit, change status, delete
 *  - engineer : view, create, edit, change status (bonus role, cannot delete)
 *  - viewer   : view only
 */
class MedicalCasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MedicalCase $case): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role->canManageCases();
    }

    /** Covers editing fields and changing the status. */
    public function update(User $user, MedicalCase $case): bool
    {
        return $user->role->canManageCases();
    }

    public function delete(User $user, MedicalCase $case): bool
    {
        return $user->isAdmin();
    }
}
