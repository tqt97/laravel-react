<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can delete a collection of models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can restore a collection of models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->is_admin === true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->is_admin === true
            && $user->isNot($model)
            && (! $model->is_admin || User::administrators()->count() > 1);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->is_admin === true && $model->trashed();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->is_admin === true && $user->isNot($model) && $model->trashed();
    }

    /**
     * Determine whether the user can permanently delete a collection.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->is_admin === true;
    }
}
