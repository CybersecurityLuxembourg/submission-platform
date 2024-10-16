<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Everyone can see the list of forms
    }

    public function view(User $user, Form $form): bool
    {
        return $user->isAdmin() ||
            $user->id === $form->user_id ||
            $form->appointedUsers->contains($user) ||
            $form->submissions()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->role === 'internal_evaluator';
    }

    public function update(User $user, Form $form): bool
    {
        return $user->isAdmin() ||
            $user->id === $form->user_id ||
            $form->appointedUsers()->where('user_id', $user->id)->where('can_edit', true)->exists();
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->isAdmin() || $user->id === $form->user_id;
    }

    public function appointUsers(User $user, Form $form): bool
    {
        return $user->isAdmin() || $user->id === $form->user_id;
    }
}
