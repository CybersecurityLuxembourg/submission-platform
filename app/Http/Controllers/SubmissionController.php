<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Submission;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    /**
     * Display the specified form for submission.
     */
    public function show(Form $form): View|Factory|Application
    {
        if ($form->status !== 'published') {
            abort(404);
        }

        return view('submissions.create', compact('form'));
    }

    /**
     * Store a newly created submission in storage.
     */
    public function store(Request $request, Form $form): \Illuminate\Http\RedirectResponse
    {
        if ($form->status !== 'published') {
            abort(404);
        }

        $fields = $form->fields;

        $rules = [];
        foreach ($fields as $field) {
            $rule = [];
            if ($field->required) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }
            // Additional validation based on field type can be added here
            $rules['field_' . $field->id] = $rule;
        }

        $validatedData = $request->validate($rules);

        $submission = $form->submissions()->create();

        foreach ($fields as $field) {
            $value = $validatedData['field_' . $field->id] ?? null;
            $submission->values()->create([
                'form_field_id' => $field->id,
                'value' => $value,
            ]);
        }

        return redirect()->route('submissions.thankyou')->with('success', 'Submission successful.');
    }

    /**
     * Display a thank you page after submission.
     */
    public function thankyou(): View|Factory|Application
    {
        return view('submissions.thankyou');
    }

    /**
     * Display a listing of submissions for a form.
     */
    public function index(Form $form): View|Factory|Application
    {
       # $this->authorize('view', $form);

        $submissions = $form->submissions()->latest()->get();

        return view('submissions.index', compact('form', 'submissions'));
    }

    /**
     * Display the specified submission.
     */
    public function showSubmission(Form $form, Submission $submission): View|Factory|Application
    {
       # $this->authorize('view', $form);

        return view('submissions.show', compact('form', 'submission'));
    }
}
