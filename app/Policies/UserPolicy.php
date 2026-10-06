<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Demo;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_user');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $currentUser): bool
    {
        return $user->can('view_any_user') || ($user->can('view_user') && $user == $currentUser);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('{{ Create }}');
    }

    /**
     * Determine whether the user can update the model. Shared demo accounts stay as seeded.
     */
    public function update(User $user, User $model): bool
    {
        return ! Demo::protects($model) && $user->can('update_user');
    }

    /**
     * Determine whether the user can delete the model. Shared demo accounts can't be deleted.
     */
    public function delete(User $user, User $model): bool
    {
        return ! Demo::protects($model) && $user->can('delete_user');
    }

    /**
     * Determine whether the user can bulk delete (off in demo mode, where a selection could include demo accounts).
     */
    public function deleteAny(User $user): bool
    {
        return ! Demo::enabled() && $user->can('delete_any_user');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user): bool
    {
        return $user->can('{{ ForceDelete }}');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('{{ ForceDeleteAny }}');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user): bool
    {
        return $user->can('{{ Restore }}');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('{{ RestoreAny }}');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function replicate(User $user): bool
    {
        return $user->can('{{ Replicate }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('{{ Reorder }}');
    }
}
