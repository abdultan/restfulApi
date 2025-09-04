<?php

namespace App\Policies;

use App\Models\Rezervation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RezervationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Rezervation  $rezervation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Rezervation $rezervation)
    {
        return $rezervation->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user !== null;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Rezervation  $rezervation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Rezervation $rezervation)
    {
        return $rezervation->user_id === $user->id && $rezervation->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Rezervation  $rezervation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Rezervation $rezervation)
    {
        return $rezervation->user_id === $user->id && $rezervation->status === 'pending';
    }

    /**
     * Confirm reservation (custom ability).
     */
    public function confirm(User $user, Rezervation $rezervation): bool
    {
        return $rezervation->user_id === $user->id && $rezervation->status === 'pending';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Rezervation  $rezervation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Rezervation $rezervation)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Rezervation  $rezervation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Rezervation $rezervation)
    {
        //
    }
}
