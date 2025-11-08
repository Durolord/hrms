<?php

namespace App\Policies;

use App\Models\Opening;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpeningPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    /**
     * Determine whether the user can view the model.
     */
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_opening');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Opening $opening): bool
    {
        return $user->can('update_opening');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Opening $opening): bool
    {
        return $user->can('delete_opening');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_opening');
    }
}
