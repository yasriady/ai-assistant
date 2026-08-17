<?php

namespace App\Policies;

use App\Models\Rubric;
use App\Models\User;

class RubricPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Rubric $rubric): bool
    {
        return $user->isAdmin() || $rubric->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Rubric $rubric): bool
    {
        return $user->isAdmin() || $rubric->user_id === $user->id;
    }

    public function delete(User $user, Rubric $rubric): bool
    {
        return $user->isAdmin() || $rubric->user_id === $user->id;
    }
}
