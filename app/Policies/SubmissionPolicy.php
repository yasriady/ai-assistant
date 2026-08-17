<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Submission $submission): bool
    {
        $submission->loadMissing('assessment');

        return $user->isAdmin() || $submission->assessment?->user_id === $user->id;
    }

    public function update(User $user, Submission $submission): bool
    {
        return $this->view($user, $submission);
    }

    public function delete(User $user, Submission $submission): bool
    {
        return $this->view($user, $submission);
    }
}
