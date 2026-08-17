<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Assessment $assessment): bool
    {
        return $user->isAdmin() || $assessment->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Assessment $assessment): bool
    {
        return $user->isAdmin() || $assessment->user_id === $user->id;
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        return $user->isAdmin() || $assessment->user_id === $user->id;
    }
}
