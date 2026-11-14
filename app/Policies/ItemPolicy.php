<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ItemPolicy
{

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Item $item): bool
    {
        return $user->id === (int) $item->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Item $item): bool
    {
        return $this->update($user, $item);
    }

    //runs before every other method of this policy
    //true => allow right away (admin can do anything with items)
    //null => no opinion, so update()/delete() below decide
    //never false, it would deny everyone, including the owner
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return null;
    }
}
