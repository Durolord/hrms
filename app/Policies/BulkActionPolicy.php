<?php

namespace App\Policies;

use App\Models\User;
use Bytexr\QueueableBulkActions\Models\BulkAction;
use Illuminate\Auth\Access\HandlesAuthorization;

class BulkActionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BulkAction $bulkAction): bool
    {
        return $user->can('view_any_bulk::action') || ($user->can('view_bulk::action') && $user->id == $bulkAction->user_id);
    }
}
