<?php

namespace App\Policies;

use App\Models\QuestionBank;
use App\Models\User;

class QuestionBankPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, QuestionBank $questionBank): bool
    {
        return $user->isAdmin() || $questionBank->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, QuestionBank $questionBank): bool
    {
        return $user->isAdmin() || $questionBank->user_id === $user->id;
    }

    public function delete(User $user, QuestionBank $questionBank): bool
    {
        return $user->isAdmin() || $questionBank->user_id === $user->id;
    }
}
