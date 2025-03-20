<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Submission;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View|Factory|Application
    {
        // Get forms created by the user
        $createdForms = Auth::user()->forms()->latest();

        // Get forms the user is assigned to
        $assignedForms = Form::whereHas('appointedUsers', function($query) {
            $query->where('user_id', Auth::id());
        })->latest();

        // Combine both queries and get the results
        $forms = $createdForms->union($assignedForms)->get();

        // Get statistics for each form
        $formStats = $forms->map(function ($form) {
            $draftCount = $form->submissions()
                ->whereIn('status', ['draft', 'ongoing'])
                ->count();

            $submittedCount = $form->submissions()
                ->where('status', 'submitted')
                ->count();

            return [
                'form' => $form,
                'draft_count' => $draftCount,
                'submitted_count' => $submittedCount,
            ];
        });

        return view('dashboard', compact('formStats'));
    }
} 