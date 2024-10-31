<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{

    /**
     * Determine whether the user can download file submissions.
     */
    public function downloadFile(User $user, Submission $submission): bool
    {
        $form = $submission->form;

        // Form owner can download
        if ($user->id === $form->user_id) {
            return true;
        }

        // Submission owner can download their own files
        if ($submission->user_id === $user->id) {
            return true;
        }

        // Appointed users with edit permissions can download
        return $form->appointedUsers()
            ->where('user_id', $user->id)
            ->where('can_edit', true)
            ->exists();
    }

}
