<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Submission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    use AuthorizesRequests;
    public function show(Form $form): View|Factory|Application
    {
        if ($form->status !== 'published') {
            abort(404);
        }

        // Load the form with its categories and fields, properly ordered
        $form->load([
            'categories' => function ($query) {
                $query->orderBy('order');
            },
            'categories.fields' => function ($query) {
                $query->orderBy('order');
            }
        ]);

        // Prepare data for the progress bar
        $progressData = [
            'totalSteps' => $form->categories->count(),
            'steps' => $form->categories->map(fn($category) => [
                'name' => $category->name,
                'description' => $category->description
            ])->values()
        ];

        return view('submissions.create', compact('form', 'progressData'));
    }

    public function store(Request $request, Form $form): RedirectResponse
    {
        if ($form->status !== 'published') {
            abort(404);
        }

        $categories = $form->categories()->with('fields')->get();

        $rules = [];
        foreach ($categories as $category) {
            foreach ($category->fields as $field) {
                $rule = [];
                if ($field->required) {
                    $rule[] = 'required';
                } else {
                    $rule[] = 'nullable';
                }

                if ($field->type === 'file') {
                    $rule[] = 'file';
                    $rule[] = 'max:10240'; // 10MB max file size
                    $rule[] = 'mimes:jpeg,png,pdf,doc,docx,xls,xlsx'; // Allowed file types
                }

                $rules['field_' . $field->id] = $rule;
            }
        }

        $validatedData = $request->validate($rules);

        $submission = $form->submissions()->create([
            'user_id' => auth()->id(), // This will be null for guest users
        ]);

        foreach ($categories as $category) {
            foreach ($category->fields as $field) {
                $value = $validatedData['field_' . $field->id] ?? null;

                if ($field->type === 'file' && $request->hasFile('field_' . $field->id)) {
                    $file = $request->file('field_' . $field->id);
                    $path = $file->store('submissions/' . $submission->id, 'private'); // Store in private storage
                    $value = $path;
                }

                $submission->values()->create([
                    'form_field_id' => $field->id,
                    'value' => $value,
                ]);
            }
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
     * @throws AuthorizationException
     */
    public function index(Form $form): View|Factory|Application
    {
        $this->authorize('view', $form);

        $submissions = $form->submissions()->latest()->get();

        return view('submissions.index', compact('form', 'submissions'));
    }

    /**
     * Display the specified submission.
     */
    public function showSubmission(Form $form, Submission $submission): View
    {
        // Ensure the submission belongs to the form
        if ($submission->form_id !== $form->id) {
            abort(404);
        }

        // Load the form with its categories and fields
        $form->load([
            'categories' => function ($query) {
                $query->orderBy('order');
            },
            'categories.fields' => function ($query) {
                $query->orderBy('order');
            },
        ]);

        // Load the submission values
        $submission->load('values');

        // Key the submission values by 'form_field_id' for easy access
        $submissionValues = $submission->values->keyBy('form_field_id');

        // Prepare categories with their fields and values
        $categories = $form->categories->map(function ($category) use ($submissionValues, $submission) {
            // Map over the category's fields
            $fields = $category->fields->map(function ($field) use ($submissionValues, $submission) {
                $value = $submissionValues->get($field->id);

                $displayValue = null;
                if ($value) {
                    $displayValue = match ($field->type) {
                        'file' => $value->value ? route('submissions.download', ['submission' => $submission->id, 'filename' => basename($value->value)]) : null,
                        'checkbox' => $value->value ? 'Yes' : 'No',
                        'radio', 'select' => $value->value,
                        default => $value->value,
                    };
                }

                // Return an array with field data and displayValue
                return [
                    'label' => $field->label,
                    'type' => $field->type,
                    'displayValue' => $displayValue,
                ];
            });

            // Return an array with category data and its fields
            return [
                'name' => $category->name,
                'description' => $category->description,
                'fields' => $fields,
            ];
        });

        return view('submissions.show', compact('form', 'submission', 'categories'));
    }


    /**
     * Display a listing of submissions for the authenticated user.
     */
    public function showUserSubmission(): View|Factory|Application
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        $submissions = Submission::where('user_id', $user->id)
            ->with('form')
            ->latest()
            ->get();

        return view('submissions.user_index', compact('submissions'));
    }

    /**
     * @throws AuthorizationException
     */
    public function downloadFile(Submission $submission, $filename): StreamedResponse
    {
        $this->authorize('generalPolicy', $submission);
        $path = 'submissions/' . $submission->id . '/' . $filename;

        // Check if the file exists in private storage
        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'File not found.');
        }

        // Serve the file securely
        return Storage::disk('private')->download($path);
    }

}
