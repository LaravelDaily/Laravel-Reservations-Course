<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role_id === Role::COMPANY_OWNER->value;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Activity $activity): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role_id === Role::COMPANY_OWNER->value;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Activity $activity): bool
    {
        return $user->role_id === Role::COMPANY_OWNER->value &&
            $user->company_id === $activity->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Activity $activity): bool
    {
        return $user->role_id === Role::COMPANY_OWNER->value &&
            $user->company_id === $activity->company_id;
    }
}
