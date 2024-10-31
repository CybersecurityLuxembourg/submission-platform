<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{

    /**
     * Determine whether the user can export/download a specific submission.
     */
    public function exportDownload(User $user, Submission $submission): bool
    {
        $form = $submission->form;

        // Form owner can export any submission
        if ($user->id === $form->user_id) {
            return true;
        }

        // Submission owner can export their own submission
        if ($submission->user_id === $user->id) {
            return true;
        }

        // Internal evaluators with edit rights can export
        if ($user->role === 'internal_evaluator') {
            return $form->appointedUsers()
                ->where('user_id', $user->id)
                ->where('can_edit', true)
                ->exists();
        }

        // External evaluators can export if appointed
        if ($user->role === 'external_evaluator') {
            return $form->appointedUsers()
                ->where('user_id', $user->id)
                ->exists();
        }

        // Appointed users with edit permissions can export
        return $form->appointedUsers()
            ->where('user_id', $user->id)
            ->where('can_edit', true)
            ->exists();
    }

    /**
     * Determine whether the user can export their own submissions.
     */
    public function exportOwn(User $user, Submission $submission): bool
    {
        return $user->id === $submission->user_id;
    }



}
