<?php

namespace App\Policies;

use App\Models\ReminderTemplate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReminderTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isMaster() || $user->isSalon();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ReminderTemplate $reminderTemplate): bool
    {
        return $user->id === $reminderTemplate->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isMaster() || $user->isSalon();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ReminderTemplate $reminderTemplate): bool
    {
        return $user->id === $reminderTemplate->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ReminderTemplate $reminderTemplate): bool
    {
        return $user->id === $reminderTemplate->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ReminderTemplate $reminderTemplate): bool
    {
        return $user->id === $reminderTemplate->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ReminderTemplate $reminderTemplate): bool
    {
        return $user->id === $reminderTemplate->user_id;
    }
}
